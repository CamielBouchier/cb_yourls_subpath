<?php
/*
Plugin Name: cb_subpath
Description: This plugin enables "https://short.com/key" to be stored as e.g. "https://some.long.com/foo/bar". Then "https//short.com/key/sub/path" will be expanded to "https://some.long.com/foo/bar/sub/path".
Version: 1.0
Author: Camiel Bouchier
*/

// Note that this functionality (basically only looking to explode('/', $keyword)[0] in the
// database and add redirecting adding the [1:..] components can be done way more efficient
// with a few lines of code in the core. 
// But that seems to be not acceptable.

yourls_add_filter('shunt_keyword_is_taken', 'cb_keyword_search');
yourls_add_action('redirect_keyword_not_found', 'cb_keyword_not_found');

function cb_keyword_search( $return, $keyword, $use_cache = true ) {

    // This is a copy/paste of the existing core code, with the exception that we
    // limit the db search to the [0] component of explode('/', $keyword)
    
    // error_log("cb_keyword_search called with keyword: " . $keyword);

    $taken = false;
    // To check if a keyword is already associated with a short URL, we fetch all info 
    // matching that keyword. This will save a query in case of a redirection in yourls-go.php 
    // because info will be cached
    if ( yourls_get_keyword_infos(explode('/', $keyword)[0], $use_cache) ) {
        $taken = true;
    }

    return yourls_apply_filter( 'keyword_is_taken', $taken, $keyword );
}

function cb_keyword_not_found($data) {

    $keyword = $data[0];
    
    // error_log("cb_keyword_not_found called with data: " . $keyword);

    // Cut/paste from yourls-go.php, but now looking to the explode('/', $keyword)[0] component
    // If we can get a long URL from the DB, redirect, but add the subpath components.
    $exploded_keyword = explode('/', $keyword);
    if( $url = yourls_get_keyword_longurl( $exploded_keyword[0] ) ) {
	    for ($i=1;$i<count($exploded_keyword);$i++) {
	        $url = $url . "/" . $exploded_keyword[$i];
	    }
    yourls_redirect_shorturl($url, $keyword);
    exit();
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////

// vim: syntax=php ts=4 sw=4 sts=4 sr et columns=100
