<?

class Viewlib
{

    function show($templates, $data)
    {
        $CI = &get_instance();
        $CI->load->view($templates, $data);
    }


}

?>