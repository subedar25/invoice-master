function selectAll(element) {
    let parentId = element.id;
    if (element.checked) {
        document
            .querySelectorAll(`.child[data-parent="${parentId}"]`)
            .forEach((child) => {
                child.checked = element.checked;
            });
    } else {
        document
            .querySelectorAll(`.child[data-parent="${parentId}"]`)
            .forEach((child) => {
                child.checked = element.checked;
            });
    }
}

function checkAllBox() {
    $(".child").each(function (index, element) {
        let parentId = this.dataset.parent;
        let parentCheckbox = document.getElementById(parentId);
        let allChildren = document.querySelectorAll(
            `.child[data-parent="${parentId}"]`
        );
        let allChecked = Array.from(allChildren).every((chk) => chk.checked);

        parentCheckbox.checked = allChecked;
    });
}

// Auto-select list/view permission when any other permission in the module is selected
$(document).on('change', '.permission-checkbox', function () {
    const $current = $(this);
    const moduleId = $current.data('module-id');
    const permName = ($current.data('permission-name') || '').toString();
    if (!moduleId || !permName) {
        return;
    }

    const isListPermission = permName.startsWith('list-') || permName.startsWith('view-');
    const $modulePermissions = $(`.permission-checkbox[data-module-id="${moduleId}"]`);

    if ($current.prop('checked') && !isListPermission) {
        const $listPermission = $modulePermissions.filter(function () {
            const name = ($(this).data('permission-name') || '').toString();
            return name.startsWith('list-') || name.startsWith('view-');
        }).first();
        if ($listPermission.length && !$listPermission.prop('checked')) {
            $listPermission.prop('checked', true);
        }
    }

    if (isListPermission && !$current.prop('checked')) {
        const hasOtherChecked = $modulePermissions.filter(function () {
            const name = ($(this).data('permission-name') || '').toString();
            return !name.startsWith('list-') && !name.startsWith('view-') && $(this).prop('checked');
        }).length > 0;
        if (hasOtherChecked) {
            $current.prop('checked', true);
        }
    }
});
