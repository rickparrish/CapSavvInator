$(document).ready(function () {
    if (localStorage.getItem('APIKey') !== null) {
        $('#txtAPIKey').val(localStorage.APIKey);
        RefreshUsage();
    }

    // See if we should load a specific panel
    if (location.hash) {
        var Panel = location.hash.replace('#', '');
        if (!OpenPanel(Panel)) OpenPanel('Usage');
    } else {
        OpenPanel('Usage');
    }
    
    // See if we should display the UseHTTPS palen
    if (window.location.protocol !== 'https:') {
        $('#pnlUseHTTPS').show();
    }
});

$('#cmdSaveAPIKey').click(function () {
    localStorage.APIKey = $.trim($('#txtAPIKey').val());
    RefreshUsage();
});

function OpenPanel(id) {
    if (!$('#' + id + 'Panel').is(":visible")) {
        $('div[id$="Panel"]').hide('slow');
        $('#' + id + 'Panel').show('slow');
        return true;
    }

    return false;
}

function RefreshUsage() {
    // Display loading animation
    $('#pnlNoData').hide('fast');
    $('#pnlUsageData').hide('fast');
    $('#pnlLoadingData').show('fast');

    // Send an AJAX request
    $.post("//csi.randm.ca/usage.php", { 'APIKey': localStorage.APIKey }, null, 'json')
        .done(function (data) {
            if (data.Success) {
				for (var prop in data) {
					if ($('#lbl' + prop).length) { $('#lbl' + prop).html(data[prop]); }
	            }

				if (!data.Uploads) {
					$('tr.Upload').hide();
					$('tr.Total').hide();
				}

				if (!data.FreePeriod) {
					$('#FreeUsage').hide();
					$('#AllUsage').hide();
				}

                $('#pnlLoadingData').hide('slow');
                $('#pnlUsageData').show('slow');
            } else {
                $('#lblISP').html(data.ISP);
                $('#pnlLoadingData').hide('slow');
                $('#pnlNoData').show('slow');
            }
        })
        .fail(function () {
            $('#lblISP').text('');
            $('#pnlLoadingData').hide('slow');
            $('#pnlNoData').show('slow');
        });
}
