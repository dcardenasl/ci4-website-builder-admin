<?php
$role = $role ?? [];
$applications = $applications ?? [];
$assignedIds = $assignedIds ?? [];
$roleId = (string) ($role['id'] ?? '');
?>

<form method="post" action="<?= route_to('admin.iam.role_permissions.save', $roleId) ?>" class="space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="code" value="<?= esc((string) ($role['code'] ?? '')) ?>">
    <input type="hidden" name="name" value="<?= esc((string) ($role['name'] ?? '')) ?>">
    <input type="hidden" name="description" value="<?= esc((string) ($role['description'] ?? '')) ?>">
    <input type="hidden" name="application_id" value="<?= esc((string) ($role['application_id'] ?? '')) ?>">
    <input type="hidden" name="permission_ids[]" value="">

    <?php foreach ($applications as $application): ?>
        <?php $permissions = $application['permissions'] ?? []; ?>
        <div class="border-b border-gray-200 pb-4">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900"><?= esc((string) ($application['name'] ?? $application['code'] ?? '')) ?></h3>
                <span class="text-xs text-gray-500"><?= count($permissions) ?> <?= esc(lang('Iam.permissions_title')) ?></span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
                <?php foreach ($permissions as $permission): ?>
                    <?php $permissionId = (string) ($permission['id'] ?? ''); ?>
                    <label class="inline-flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
                        <input type="checkbox" name="permission_ids[]" value="<?= esc($permissionId) ?>"
                            <?= in_array($permissionId, $assignedIds, true) ? 'checked' : '' ?>
                            class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span>
                            <code class="font-medium text-gray-900"><?= esc((string) ($permission['code'] ?? '-')) ?></code>
                            <span class="block text-xs text-gray-500"><?= esc((string) ($permission['description'] ?? '')) ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.save')) ?></button>
</form>
