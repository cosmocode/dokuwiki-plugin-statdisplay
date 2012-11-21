<?php
    /**
    * Metadata for configuration manager plugin
    * Additions for the StatDisplay plugin
    *
    * @author    Maxime FONDA && Thibault COULLET
    */

    $meta['accesslog']    = array('string');
    $meta['auto_compute_stats'] = array('onoff');
    $meta['line_number'] = array('numeric');
    $meta['visit_time'] = array('numeric');
    $meta['top_url_number_of_lines'] = array('numeric');
    $meta['top_kbytes_number_of_lines'] = array('numeric');
    $meta['top_entries_number_of_lines'] = array('numeric');
    $meta['referer'] = array('multichoice','_choices' => array('complete_link','domain'));
    $meta['top_referers_number_of_lines'] = array('numeric');
    $meta['regular_use'] = array('onoff');
    $meta['referer_regular_expr'] = array('');
    $meta['user_agent'] = array('multichoice','_choices' => array('all_line','display','keyword'));
    $meta['top_user_agents_number_of_lines'] = array('numeric');
    $meta['user_agent_keywords'] = array('');
    $meta['memory_cache']    = array('string');
?>
