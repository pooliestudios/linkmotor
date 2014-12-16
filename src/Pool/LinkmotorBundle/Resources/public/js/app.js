$(function() {
    var poolAjaxRefreshInterval;

    var poolLinkmotorModal;
    var slowModal = new $.UIkit.modal.Modal("#slow-modal");
    var finishInstallationModal = new $.UIkit.modal.Modal('#finish-installation-modal');

    if ($('#finish-installation-modal').length == 1) {
        finishInstallationModal.show();
    }

    if (typeof poolIsLoggedIn != 'undefined' && poolIsLoggedIn) {
        setInterval(poolLinkmotorReloadProjectAlertBadge, 60000); // 60s
    }

    $('.hidden').each(function() {
        $(this).hide();
    });

    $('.uk-close').click(function() {
        $(this).parents('.uk-alert').hide('slow');
        return false;
    });

    $('form.slow').submit(function() {
        slowModal.show();
    });
    $('a.slow').click(function() {
        slowModal.show();
    });


    $('table.draggable').dragtable({
        dragaccept:'.draggable-column',
        dragHandle:'.drag-handle',
        persistState: function(table) {
            table.el.find('th').each(function(i) {
                if (this.id != '') {
                    table.sortOrder[this.id] = i;
                }
            });
            if ($('th.bulk-actions.pages').length == 1) {
                poolTableOptions('pages', 'sort-order', table.sortOrder);
            } else if ($('th.bulk-actions.backlinks').length == 1) {
                poolTableOptions('backlinks', 'sort-order', table.sortOrder);
            }
        }
    });
    $('.column-options li a').click(function() {
        var id = $(this).attr('data-id');
        var action = '';
        var currentIcon = $(this).find('i').prop('class');

        if (currentIcon == 'uk-icon-check-square-o') {
            $('th#row-' + id).hide();
            $('td.row-' + id).hide();
            $(this).find('i').prop('class', 'uk-icon-square-o');
            action = 'hide';
        } else {
            $('th#row-' + id).show();
            $('td.row-' + id).show();
            action = 'show';
            $(this).find('i').prop('class', 'uk-icon-check-square-o');
        }

        if ($('th.bulk-actions.pages').length == 1) {
            poolTableOptions('pages', action, id);
        } else if ($('th.bulk-actions.backlinks').length == 1) {
            poolTableOptions('backlinks', action, id);
        }

        return false;
    });

    var sortableTables = $('.sortable-table').stupidtable();
    sortableTables.on("aftertablesort", function (event, data) {
        var th = $(this).find("th");
        th.find(".arrow").remove();
        var dir = $.fn.stupidtable.dir;

        var arrow = data.direction === dir.ASC ? "&uarr;" : "&darr;";
        th.eq(data.column).append('<span class="arrow">' + arrow +'</span>');
    });

    if (typeof poolCharts != 'undefined') {
        for (var i=0; i<poolCharts.length; i++) {
            poolCharts[i]();
        }
    }

    $('.pool-datetime-presets').find('a').click(function() {
        var presetElement = $(this).parents('.pool-datetime-presets');
        $(presetElement).find('li.uk-active').removeClass('uk-active');
        $(this).parent().addClass('uk-active');

        $.get(poolRouteAlertHideUntilPreset + '?value=' + $(this).attr('pool-datetime-preset'), function(data) {
            $('#' + presetElement.attr('rel') + '_form_row').html(data);
        });

        return false;
    });

    $("ul.filter select").chosen({disable_search_threshold: 6});

    $('form.autosubmit').change(function() {
        $(this).submit();
    });

    $('select.backlink-cost-type').change(poolLinkmotorCheckBacklinkFormPrice);
    if ($('select.backlink-cost-type').length > 0) {
        poolLinkmotorCheckBacklinkFormPrice();
    }
    $('select.crawltype-switcher').change(function() {
        if (this.value == 'dom') {
            $('.crawltype-container-dom').show();
            $('.crawltype-container-text').hide();
        } else {
            $('.crawltype-container-dom').hide();
            $('.crawltype-container-text').show();
        }
    });

    $('select[name=changeDateFilter]').change(function() {
        if (this.value == 'manual') {
            poolLinkmotorModal = new $.UIkit.modal.Modal('#filter-date-range-modal');
            poolLinkmotorModal.show();
            var lastValue = $('#changeDateFilter').attr('x-data-last-value');
            $('#changeDateFilter option').eq(lastValue).prop('selected', true);
            $('#changeDateFilter').trigger('chosen:updated');
        } else {
            $(this).parents('form').submit();
        }
    });

    $('#filter-manual-date-range-form').submit(function() {
        poolLinkmotorModal.close();
        return false;
    });

    poolFavicon = new Favico({animation:'none'});
    if (typeof poolFaviconBadge != 'undefined' && poolFaviconBadge > 0) {
        poolFavicon.badge(poolFaviconBadge);
    }

    poolAjaxRefreshInterval = setInterval(function() {
        poolRefreshImports();
    }, 5000);

    poolSetAnchorTextInForm();
    $('#backlink_type').change(poolSetAnchorTextInForm);
    $('#backlink_add_type').change(poolSetAnchorTextInForm);

    $('.pool-notification-settings-check').each(function() {
        poolNotificationSettingsCheck(
            $(this).attr('data-pool-project-which'),
            $(this).attr('data-pool-project-id'),
            $(this).prop('checked')
        );
    });
    $('.pool-notification-settings-check').click(function() {
        poolNotificationSettingsCheck(
            $(this).attr('data-pool-project-which'),
            $(this).attr('data-pool-project-id'),
            $(this).prop('checked')
        );

        return true;
    });

    $('.pool-toggle').each(function() {
        poolAdminSettingsCheck($(this).attr('x-data-pool-toggle'), $(this).prop('checked'));
    });
    $('.pool-toggle').click(function() {
        poolAdminSettingsCheck($(this).attr('x-data-pool-toggle'), $(this).prop('checked'));
    });

    poolCheckFormAccountType();
    $('#form-account-type').change(poolCheckFormAccountType);

    if (typeof poolRoute != 'undefined' && typeof poolIntros[poolRoute] != 'undefined') {
        var introOptions = poolIntros[poolRoute];
        var autostart = typeof introOptions.autostart != 'undefined' && introOptions.autostart == true;
        if (poolRoute == 'pool_linkmotor_project_dashboard' && !poolShowDashboardTour) {
            autostart = false;
        }
        if (autostart || window.location.hash == '#start-tour') {
            var steps = [];
            for (i=0; i<introOptions.steps.length; i++) {
                if (typeof introOptions.steps[i].onlyForAdmin == 'undefined' || poolIsAdmin) {
                    introOptions.steps[i].element = document.querySelector(introOptions.steps[i].element);
                    steps.push(introOptions.steps[i]);
                }
            }
            introOptions.steps = steps;

            var intro = introJs();
            intro.setOptions(introOptions);
            intro.start();
        }
    }

    poolCheckPageBulkActionOptions();
    poolCheckBacklinkBulkActionOptions();
    $('#set-bulk-actions').change(function() {
        $('td.bulk-actions :checkbox').prop('checked', $(this).prop('checked'));
        poolCheckPageBulkActionOptions();
        poolCheckBacklinkBulkActionOptions();
    });
    $('td.bulk-actions :checkbox').change(function() {
        poolCheckPageBulkActionOptions();
        poolCheckBacklinkBulkActionOptions();
    });

    $('.bulk-actions-form form').submit(function() {
        if ($('.bulk-actions-form select').val() == 'delete-delete') {
            if (!confirm(poolTranslations['Are you sure?'])) {
                $('.bulk-actions-form select').val('');
                return false;
            }
        }
        slowModal.show();

        return true;
    });
});

function poolCheckFormAccountType()
{
    if ($('input[name="admin_account[account_type]"]:checked').val() == 1) {
        $('#account-invoice-address').show();
        $('.account-save').hide();
    } else {
        $('#account-invoice-address').hide();
        $('.account-save').show();
    }
}

function poolCheckPageBulkActionOptions()
{
    if ($('td.bulk-actions :checked').length > 0) {
        $('#page_bulk_actions_bulkAction').prop('disabled', false);
        var bulkItemIds = [];
        var mayChangeStatus = true;
        var mayDelete = true;
        $('td.bulk-actions :checked').each(function() {
            bulkItemIds.push($(this).attr('data-id'));
            if ($(this).attr('data-may-change-status') == '0') {
                mayChangeStatus = false;
            }
            if ($(this).attr('data-has-backlink') == '1') {
                mayDelete = false;
            }
        });
        $('#page_bulk_actions_bulkItems').val(bulkItemIds.join(','));
        $('#page_bulk_actions_bulkAction > optgroup:nth-child(2)').prop('disabled', !mayChangeStatus);
        $('#page_bulk_actions_bulkAction > optgroup:nth-child(4)').prop('disabled', !mayDelete);
    } else {
        $('#page_bulk_actions_bulkAction').prop('disabled', true);
        $('#page_bulk_actions_bulkItems').val('');
    }
}

function poolCheckBacklinkBulkActionOptions()
{
    if ($('td.bulk-actions :checked').length > 0) {
        $('#backlink_bulk_actions_bulkAction').prop('disabled', false);
        var bulkItemIds = [];
        var mayChangeStatus = true;
        var mayDelete = true;
        var hasAlert = true;
        $('td.bulk-actions :checked').each(function() {
            bulkItemIds.push($(this).attr('data-id'));
            if ($(this).attr('data-may-change-status') == '0') {
                mayChangeStatus = false;
            }
            if ($(this).attr('data-has-alert') == '0') {
                hasAlert = false;
            }
        });
        $('#backlink_bulk_actions_bulkItems').val(bulkItemIds.join(','));
        $('#backlink_bulk_actions_bulkAction > optgroup:nth-child(2)').prop('disabled', !mayChangeStatus);
        $('#backlink_bulk_actions_bulkAction > optgroup:nth-child(4)').prop('disabled', !hasAlert);
        $('#backlink_bulk_actions_bulkAction > optgroup:nth-child(5)').prop('disabled', !mayDelete);
    } else {
        $('#backlink_bulk_actions_bulkAction').prop('disabled', true);
        $('#backlink_bulk_actions_bulkItems').val('');
    }
}

function poolAdminSettingsCheck(targetElementId, checked)
{
    if (checked) {
        $('#' + targetElementId).show();
    } else {
        $('#' + targetElementId).hide();
    }
}

function poolNotificationSettingsCheck(which, id, checked)
{
    if (checked) {
        $('#pool-notification-settings-' + which +'-' + id).show();
    } else {
        $('#pool-notification-settings-' + which +'-' + id).hide();
    }
}

function poolSetAnchorTextInForm()
{
    var formName = 'backlink';
    if ($('#backlink_add_anchor').length) {
        formName = 'backlink_add';
    }

    if ($('#' + formName + '_anchor').length) {
        var anchorLabel = poolTranslate('Anchor');
        var anchorHelp = poolTranslate('Exact anchor text (including markup)');
        if ($('#' + formName + '_type').val() == 'i') {
            anchorLabel = poolTranslate('Alt-Text');
            anchorHelp = poolTranslate('Exact Alt-Text (including markup)');
        }
        $('label[for="' + formName + '_anchor"]').html(anchorLabel);
        $('#backlink_add_anchor_help').html(anchorHelp);
    }
}

function poolTranslate(key)
{
    if (typeof poolTranslations[key] == 'undefined') {
        return key;
    }

    return poolTranslations[key];
}

function poolRefreshImports()
{
    $('tr.refresh-import').each(function() {
        var row = $(this);
        $.get(poolRouteAjaxRefreshImport.replace('__id__', row.attr('lm-data')), function(data) {
            row.replaceWith(data);
        });
    });
}

function poolTableOptions(which, action, value)
{
        var url = poolRouteAjaxTableOptions
            .replace('__which__', which)
            .replace('__action__', action);

        $.post(url, {value:value});
}

$(window).on('load', function() {
    // Check all tables. You may need to be more restrictive.
    $('table.scrollable').each(function() {
        var element = $(this);
        // Create the wrapper element
        var scrollWrapper = $('<div />', {
            'class': 'scrollable',
            'html': '<div />' // The inner div is needed for styling
        }).insertBefore(element);
        // Store a reference to the wrapper element
        element.data('scrollWrapper', scrollWrapper);
        // Move the scrollable element inside the wrapper element
        element.appendTo(scrollWrapper.find('div'));
        // Check if the element is wider than its parent and thus needs to be scrollable
        if (element.outerWidth() > element.parent().outerWidth()) {
            element.data('scrollWrapper').addClass('has-scroll');
        }
        // When the viewport size is changed, check again if the element needs to be scrollable
        $(window).on('resize orientationchange', function() {
            if (element.outerWidth() > element.parent().outerWidth()) {
                element.data('scrollWrapper').addClass('has-scroll');
            } else {
                element.data('scrollWrapper').removeClass('has-scroll');
            }
        });
    });
});

function poolLinkmotorReloadProjectAlertBadge()
{
    $.get(poolRouteProjectAlertBadge, function(data) {
        $('#project-alert-badge').html(data);
        poolFaviconBadge = $('#project-alert-badge .uk-badge').text();
        poolFavicon.badge(poolFaviconBadge);
    });
}

function poolLinkmotorCheckBacklinkFormPrice()
{
    var costType = $('select.backlink-cost-type').val();

    switch (costType) {
        case '0':
            $('#backlink-cost-type-money').hide();
            $('#backlink-cost-type-other').hide();
            break;
        case '1':
        case '2':
        case '3':
            $('#backlink-cost-type-money').show();
            $('#backlink-cost-type-other').show();
            break;
        case '4':
        case '5':
            $('#backlink-cost-type-money').hide();
            $('#backlink-cost-type-other').show();
            break;
    }
}
