function get_reporters(process, id, reason, page) {
    var url = base_url + 'admin/report_ajax.php?id='+id+'&process='+process+'&p='+page+'&reason='+reason+"&key="+base_key;
    $e = $('#cboxContent');
    $e.load(url, function () {
        $e.trigger("DOMChanged", $e);
    });
}