<?php
/**
 * @author captain-redbeard
 * @since 20/01/17
 */
namespace Redbeard\Crew;

use Redbeard\Crew\Config;
use Redbeard\Crew\Database;
use Redbeard\Crew\Utils\Strings;

class Pagination
{
    public static function getLimit($table_name, $condition = 'WHERE cancelled = 0')
    {
        //Get current page
        $current_page = isset($_GET['page']) ? Strings::cleanInput($_GET['page'], 2) : 1;
        
        //Get setting model
        $setting = Config::get('app.system_path') . Config::get('app.models_path') . 'Setting';
        $setting = new $setting;
        $results_per_page = $setting->getSettingById(1)->value;
        $range = $setting->getSettingById(2)->value;
        
        //Get row count
        $counter = Database::select(
            "SELECT COUNT(1) FROM $table_name $condition;",
            []
        )[0]['COUNT(1)'];
        
        //Total pages
        $total_pages = ceil($counter / $results_per_page);
        
        //Offset
        $offset = ($current_page - 1) * $results_per_page;
        
        //Return
        return [
            'range' => $range,
            'results_per_page' => $results_per_page,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'offset' => $offset
        ];
    }
}
