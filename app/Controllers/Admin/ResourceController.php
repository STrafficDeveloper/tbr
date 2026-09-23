<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Admin\FormHandler;
use App\Admin\Resource;
use App\Admin\ResourceRepository;
use App\Admin\Resources;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Services\ImageUploader;
use RuntimeException;

/** List, create, edit and delete for every Resource in App\Admin\Resources. */
final class ResourceController extends AdminController
{
    private const PER_PAGE = 25;

    private ResourceRepository $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = new ResourceRepository();
    }

    public function index(Request $request): string
    {
        $resource = $this->resource($request);
        $parent = $this->parent($resource, $request);
        $search = mb_substr((string) ($request->input('q') ?? ''), 0, 100);
        $parentId = $parent === null ? null : (int) $parent['id'];

        $paginator = new Paginator(
            $this->repository->count($resource, $parentId, $search),
            self::PER_PAGE,
            $request->page(),
            '/admin/urus/' . $resource->key,
            ['q' => $search, 'parent' => $parentId === null ? '' : (string) $parentId],
        );

        return $this->adminView('resource-index', $resource->label, [
            'resource' => $resource,
            'parent' => $parent,
            'parentResource' => $resource->parentKey !== null ? Resources::get($resource->parentKey) : null,
            'rows' => $this->repository->list($resource, $parentId, $search, self::PER_PAGE, $paginator->offset()),
            'paginator' => $paginator,
            'search' => $search,
        ]);
    }

    public function create(Request $request): string
    {
        $resource = $this->resource($request);
        $parent = $this->parent($resource, $request);

        return $this->adminView('resource-form', 'Tambah ' . $resource->singular, [
            'resource' => $resource,
            'parent' => $parent,
            'row' => null,
            'childCounts' => [],
        ]);
    }

    public function store(Request $request): never
    {
        $this->verifyCsrf($request);
        $resource = $this->resource($request);
        $parent = $this->parent($resource, $request);
        $parentId = $parent === null ? null : (int) $parent['id'];

        $handler = new FormHandler($this->repository);
        $result = $handler->handle($resource, $_POST, $_FILES, null, $parentId);

        if ($result['errors'] !== []) {
            $this->backWithErrors($this->url($resource, 'baru', $parentId), $result['errors'], $this->oldInput($resource));
        }

        $data = $result['data'];
        if ($resource->foreignKey !== null) {
            $data[$resource->foreignKey] = $parentId;
        }

        try {
            $id = $this->repository->insert($resource, $data);
        } catch (\Throwable $exception) {
            $handler->discardNewFiles();
            throw $exception;
        }

        $handler->commit();
        $this->redirectWithStatus($this->url($resource, (string) $id, $parentId), ucfirst($resource->singular) . ' telah ditambah.');
    }

    public function edit(Request $request): string
    {
        $resource = $this->resource($request);
        $row = $this->row($resource, $request);
        $parent = $this->parentOfRow($resource, $row);

        $childCounts = [];
        foreach ($resource->children as $childKey) {
            $child = Resources::get($childKey);
            if ($child !== null) {
                $childCounts[$childKey] = ['resource' => $child, 'count' => $this->repository->childCount($child, (int) $row['id'])];
            }
        }

        return $this->adminView('resource-form', 'Edit ' . $resource->singular, [
            'resource' => $resource,
            'parent' => $parent,
            'row' => $row,
            'childCounts' => $childCounts,
        ]);
    }

    public function update(Request $request): never
    {
        $this->verifyCsrf($request);
        $resource = $this->resource($request);
        $row = $this->row($resource, $request);
        $parent = $this->parentOfRow($resource, $row);
        $parentId = $parent === null ? null : (int) $parent['id'];

        $handler = new FormHandler($this->repository);
        $result = $handler->handle($resource, $_POST, $_FILES, $row, $parentId);
        $editUrl = $this->url($resource, (string) $row['id'], $parentId);

        if ($result['errors'] !== []) {
            $this->backWithErrors($editUrl, $result['errors'], $this->oldInput($resource));
        }

        try {
            $this->repository->update($resource, (int) $row['id'], $result['data']);
        } catch (\Throwable $exception) {
            $handler->discardNewFiles();
            throw $exception;
        }

        $handler->commit();
        $this->redirectWithStatus($editUrl, 'Perubahan telah disimpan.');
    }

    public function destroy(Request $request): never
    {
        $this->verifyCsrf($request);
        $resource = $this->resource($request);
        $row = $this->row($resource, $request);
        $parent = $this->parentOfRow($resource, $row);
        $parentId = $parent === null ? null : (int) $parent['id'];
        $indexUrl = $this->url($resource, '', $parentId);

        $refusal = $resource->deleteGuard !== null ? ($resource->deleteGuard)($row) : null;

        if ($refusal !== null) {
            $this->redirectWithStatus($this->url($resource, (string) $row['id'], $parentId), $refusal, 'error');
        }

        // The database cascades child rows, but not the image files on disk.
        $files = $this->imagePaths($resource, $row);
        foreach ($resource->children as $childKey) {
            $child = Resources::get($childKey);
            foreach ($child === null ? [] : $this->repository->children($child, (int) $row['id']) as $childRow) {
                array_push($files, ...$this->imagePaths($child, $childRow));
            }
        }

        $this->repository->delete($resource, (int) $row['id']);

        $uploader = new ImageUploader();
        foreach ($files as $path) {
            $uploader->delete($path);
        }

        $this->redirectWithStatus($indexUrl, '"' . $resource->titleOf($row) . '" telah dipadam.');
    }

    /** Several photos at once into an album or profile gallery. */
    public function bulkUpload(Request $request): never
    {
        $this->verifyCsrf($request);
        $resource = $this->resource($request);
        $parent = $this->parent($resource, $request);
        $field = $resource->bulkImageField !== null ? $resource->field($resource->bulkImageField) : null;

        if ($parent === null || $field === null) {
            Response::notFound();
        }

        $indexUrl = $this->url($resource, '', (int) $parent['id']);
        $files = $this->normaliseMultiple($_FILES['photos'] ?? null);

        if ($files === []) {
            $this->redirectWithStatus($indexUrl, 'Sila pilih sekurang-kurangnya satu gambar.', 'error');
        }

        $uploader = new ImageUploader(ImageUploader::ADMIN_MAX_BYTES);
        $saved = 0;
        $failed = [];

        foreach ($files as $file) {
            try {
                $path = $uploader->storeResized($file, str_replace('_', '-', $resource->table), $field->imageWidth);
                $this->repository->insert($resource, [
                    $resource->foreignKey => (int) $parent['id'],
                    $field->name => $path,
                ]);
                $saved++;
            } catch (RuntimeException $exception) {
                $failed[] = $file['name'] . ': ' . $exception->getMessage();
            }
        }

        $message = "{$saved} gambar dimuat naik.";
        if ($failed !== []) {
            $message .= ' Gagal: ' . implode(' ', $failed);
        }

        $this->redirectWithStatus($indexUrl, $message, $failed === [] ? 'success' : 'error');
    }

    private function resource(Request $request): Resource
    {
        $resource = Resources::get((string) $request->routeParam('resource'));

        if ($resource === null) {
            Response::notFound();
        }

        return $resource;
    }

    /** @return array<string,mixed> */
    private function row(Resource $resource, Request $request): array
    {
        $id = (int) $request->routeParam('id');
        $row = $id > 0 ? $this->repository->find($resource, $id) : null;

        if ($row === null) {
            Response::notFound();
        }

        return $row;
    }

    /**
     * A child list (e.g. a contest's prizes) must name an existing parent.
     *
     * @return array<string,mixed>|null
     */
    private function parent(Resource $resource, Request $request): ?array
    {
        if (!$resource->isChild()) {
            return null;
        }

        $parentResource = Resources::get((string) $resource->parentKey);
        $parentId = (int) ($request->input('parent') ?? 0);
        $parent = $parentResource !== null && $parentId > 0 ? $this->repository->find($parentResource, $parentId) : null;

        if ($parent === null) {
            Response::notFound();
        }

        return $parent;
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>|null
     */
    private function parentOfRow(Resource $resource, array $row): ?array
    {
        if (!$resource->isChild()) {
            return null;
        }

        return $this->repository->find(Resources::get((string) $resource->parentKey), (int) $row[$resource->foreignKey]);
    }

    private function url(Resource $resource, string $suffix, ?int $parentId): string
    {
        return '/admin/urus/' . $resource->key . ($suffix !== '' ? '/' . $suffix : '')
            . ($parentId !== null ? '?parent=' . $parentId : '');
    }

    /** @return array<string,string|null> everything typed except file inputs */
    private function oldInput(Resource $resource): array
    {
        $old = [];

        foreach ($resource->fields as $field) {
            if ($field->type === 'checkbox') {
                $old[$field->name] = isset($_POST[$field->name]) ? '1' : null;
            } elseif ($field->type !== 'image') {
                $value = $_POST[$field->name] ?? null;
                $old[$field->name] = is_string($value) ? $value : null;
            }
        }

        return $old;
    }

    /**
     * @param array<string,mixed> $row
     * @return list<string>
     */
    private function imagePaths(Resource $resource, array $row): array
    {
        $paths = [];

        foreach ($resource->imageFields() as $field) {
            if (!empty($row[$field->name])) {
                $paths[] = (string) $row[$field->name];
            }
        }

        return $paths;
    }

    /**
     * PHP's <input multiple> arrays are column-major; turn them into one array per file.
     *
     * @return list<array<string,mixed>>
     */
    private function normaliseMultiple(mixed $input): array
    {
        if (!is_array($input) || !is_array($input['name'] ?? null)) {
            return [];
        }

        $files = [];

        foreach (array_keys($input['name']) as $i) {
            if (($input['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $files[] = [
                'name' => (string) $input['name'][$i],
                'type' => $input['type'][$i] ?? '',
                'tmp_name' => $input['tmp_name'][$i] ?? '',
                'error' => $input['error'][$i],
                'size' => $input['size'][$i] ?? 0,
            ];
        }

        return $files;
    }
}
