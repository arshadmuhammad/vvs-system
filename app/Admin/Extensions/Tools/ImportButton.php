<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class ImportButton extends AbstractTool
{

    /**
     * Set up script for import button.
     */
    protected function setUpScripts()
    {
        $script = <<<SCRIPT
function handleFileSelect(evt) {
    var file = evt.target.files; // FileList object
    var fileName = file[0].name;

    $('#input').hide();
    $('#confirm').show();
    $('#confirm .text').text(fileName);
}

$('#files').on('change', handleFileSelect);

$('#confirm').on('click', function() {

    var formData = new FormData();
    if ($("input[name='csvfile']").val()!== '') {
        formData.append( "file", $("input[name='csvfile']").prop("files")[0] );
    }
    formData.append("_token", LA.token);

    $.ajax({
        method: 'POST',
        url: '/admin/pins/csv/import',
        data: formData,
        processData: false,
        contentType: false,
        success: function () {
            $.pjax.reload('#pjax-container');
            toastr.success('Uploading Success');
        }
    });
});
SCRIPT;
        Admin::script($script);
    }

    /**
     * Render Import button.
     *
     * @return string
     */
    public function render()
    {

        $this->setUpScripts();

        $import = 'Import';

        return <<<EOT

            <div class="pull-right" style="margin-right: 10px">
            <label id="input">
            <span class="btn btn-sm btn-twitter">
            <span><i class="fa fa-upload"></i> {$import}</span>
            <input type="file" id="files" name="csvfile" style="display:none">
            </span>
            </label>
            <span id="confirm" class="btn btn-sm btn-twitter" style="display:none">
            <span><i class="fa fa-upload"></i> <span class="text"></span></span>
            </span>
            </div>
EOT;

    }
}
