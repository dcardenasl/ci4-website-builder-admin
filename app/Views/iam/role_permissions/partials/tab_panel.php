<?php
$role = $role ?? [];
$applications = $applications ?? [];
$assignedIds = $assignedIds ?? [];
$roleId = (string) ($role['id'] ?? '');
?>

<?php
$initialSelected = count($assignedIds);
?>
<form method="post" action="<?= route_to('admin.iam.role_permissions.save', $roleId) ?>" class="space-y-4"
      x-data="{
          search: '',
          isDirty: false,
          selectedCount: <?= $initialSelected ?>,
          updateCount() {
              this.selectedCount = Array.from(this.$el.querySelectorAll('input[name=\'permission_ids[]\']:checked')).length;
          }
      }"
      @change="isDirty = true; updateCount()">
    <?= csrf_field() ?>
    <input type="hidden" name="code" value="<?= esc((string) ($role['code'] ?? '')) ?>">
    <input type="hidden" name="name" value="<?= esc((string) ($role['name'] ?? '')) ?>">
    <input type="hidden" name="description" value="<?= esc((string) ($role['description'] ?? '')) ?>">
    <input type="hidden" name="application_id" value="<?= esc((string) ($role['application_id'] ?? '')) ?>">
    <input type="hidden" name="permission_ids[]" value="">

    <!-- Orientation callout -->
    <div class="rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800 flex items-center justify-between gap-4">
        <p>
            <strong><?= esc((string) ($role['name'] ?? $role['code'] ?? '')) ?></strong>
            &mdash; <?= esc(lang('Iam.role_permissions_hint')) ?>
        </p>
        <span class="shrink-0 text-xs font-semibold text-blue-700 tabular-nums">
            <span x-text="selectedCount"></span> <?= esc(lang('Iam.role_permissions_selected_label')) ?>
        </span>
    </div>

    <div class="flex items-center gap-2 max-w-md">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <?= ui_icon('search', 'h-4 w-4') ?>
            </span>
            <input type="text" x-model="search" placeholder="<?= esc(lang('Iam.permissions_search_placeholder')) ?>" class="pl-9 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
        </div>
        <button type="button" x-show="search !== ''" @click="search = ''" class="text-xs text-gray-500 hover:text-gray-700" x-cloak><?= esc(lang('App.clear') ?? 'Clear') ?></button>
    </div>

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
                    <label class="inline-flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50"
                        x-show="search === '' || '<?= esc(strtolower((string) $permission['code'])) ?>'.includes(search.toLowerCase()) || '<?= esc(strtolower((string) $permission['description'])) ?>'.includes(search.toLowerCase())"
                        x-cloak>
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

    <div class="flex items-center gap-3">
        <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.save')) ?></button>
        <p x-show="isDirty" x-cloak class="text-xs font-medium text-yellow-700 flex items-center gap-1">
            <?= ui_icon('triangle-alert', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.unsaved_changes')) ?>
        </p>
    </div>
</form>
