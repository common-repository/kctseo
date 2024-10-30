jQuery(document).ready(function($) {
    $('#export_btn').on('click', function() {
        var keyword = $('#search_keyword').val();
        if (!keyword) {
            alert('Vui lòng nhập từ khoá!');
            return;
        }
        $('#loading').show();
        $.ajax({
            url: kctseo_js.ajax_url,
            type: 'POST',
            data: {
                action: 'kctseo_export_options',
                keyword: keyword,
                nonce: kctseo_js.nonce
            },
            success: function(response) {
                $('#loading').hide();
                if (response.success) {
                    // Tạo Blob và tải file
                    var jsonBlob = new Blob([JSON.stringify(response.data, null, 2)], { type: 'application/json' });
                    var downloadLink = document.createElement('a');
                    downloadLink.href = window.URL.createObjectURL(jsonBlob);

                    const date = new Date();
                    const formattedDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;
                    downloadLink.download = `options_export_${formattedDate}.json`;

                    downloadLink.click();
                    
                    $('#response').html('Export thành công!');
                } else {
                    $('#response').html(response.data);
                }
            }
        });
    });

    $('#import_btn').on('click', function() {
        $('#import_file').click();
    });

    $('#import_file').on('change', function() {
        var file = this.files[0];
        var formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'kctseo_import_options');
        formData.append('nonce', kctseo_js.nonce);

        $('#loading').show();
        $.ajax({
            url: kctseo_js.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#loading').hide();
                if (response.success) {
                    $('#response').html('Import Success!');
                } else {
                    $('#response').html(response.data);
                }
            }
        });
    });
});


jQuery(document).ready(function($) {
    $('#zip-theme-btn').on('click', function() {
        var selectedTheme = $('#theme-select').val();

        $.ajax({
            url: kctseo_js.ajax_url,
            method: 'POST',
            data: {
                action: 'zip_theme',
                theme: selectedTheme,
                nonce: kctseo_js.nonce
            },
            beforeSend: function() {
                $('#zip-theme-status').html('Processing...');
            },
            success: function(response) {
                 if (response.success) {
                        $('#zip-theme-status').html('<a class="kctseo-fl-button kctseo-fl-dm-sans kctseo-fl-transitions-2s" href="' + response.data.zip_url + '"><i class="wp-menu-image dashicons-before dashicons-download"></i> Tải file ZIP</a>');
                    } else {
                        $('#zip-theme-status').html('Error: ' + response.data.message);
                    }

            },
            error: function() {
                $('#zip-theme-status').html('There was an error');
            }
        });
    });
});


jQuery(document).ready(function($) {
            $('#zip-plugin-btn').on('click', function(e) {
                e.preventDefault();

                var pluginPath = $('#plugin-select').val();
                
                $('#zip-status').html('Creating file ZIP...');

                
                $.post(kctseo_js.ajax_url, {
                    action: 'kctseo_zip_plugin',
                    plugin: pluginPath,
                    nonce: kctseo_js.nonce
                }, function(response) {
                    if (response.success) {
                        $('#zip-status').html('<a class="kctseo-fl-button kctseo-fl-dm-sans kctseo-fl-transitions-2s" href="' + response.data.url + '"><i class="wp-menu-image dashicons-before dashicons-download"></i> Downloads file ZIP</a>');
                    } else {
                        $('#zip-status').html('Error: ' + response.data.message);
                    }
                });
            });
        });



