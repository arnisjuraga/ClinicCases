<?php

//This file includes all functions which deal with formatting dates and times.
//TODO deal with timezones

//extracts date from a mysql datestamp
function extract_date($val)
{
    // pr($val);
    //$date = date_parse($val);
    $date = new DateTime( $val );

    //echo $date->format(DATE_FORMAT) ;

    if(!$date){
        prd($val);
    }

    return $date->format(DATE_FORMAT);

}

//extracts date and time from a mysql timestamp
function extract_date_time($val)
{
    $date = date_create($val);
    return date_format($date, !defined('DATE_FORMAT_LONG') ? 'F j, Y g:i a' : DATE_FORMAT_LONG);
}

//extracts date and time from a mysql timestamp, sortable
function extract_date_time_sortable($val)
{
    $date = date_create($val);
    return date_format($date, 'd/m/Y g:i a');
}


function sql_date_to_us_date($date)
{

    if (!empty($date)) {
        $parts = explode('-', $date);
        $us_date = $parts[2] . "/" . $parts[1] . "/" . $parts[0];
        return $us_date;
    }

}

//Converts date to sql datetime
function date_to_sql_datetime($date)
{

    if (!empty($date)) {
        $parts = explode('/', $date);
        //This is a left over fix from CC6.  Ensures that casenotes entered for the same day appear in the right order
        $time_part = date('H:i:s');

        $datetime = $parts[2] . "-" . $parts[0] . "-" . $parts[1] . " " . $time_part;
        return $datetime;
    }

}
