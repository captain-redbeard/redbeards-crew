<?php
/**
 * @author captain-redbeard
 * @since 25/02/17
 */
namespace Redbeard\Crew\Models;

use Redbeard\Crew\Database;
use Redbeard\Crew\Utils\Strings;
use Redbeard\Crew\Utils\Validator;

class Setting
{
    public $id = null;
    public $name = null;
    public $description = null;
    public $value = null;
    
    public function __construct(
        $id = null,
        $name = null,
        $description = null,
        $value = null
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->value = $value;
    }
    
    public function getSettings($id_min = 1, $id_max = 999, $id = null)
    {
        //Define array
        $settings = [];
        $limit_setting = 'WHERE setting_id >= ? AND setting_id <= ?';
        
        //Limit id
        if ($id !== null) {
            $limit_setting = 'WHERE setting_id = ?';
            $parameters[0] = $id;
        } else {
            $parameters[0] = $id_min;
            $parameters[1] = $id_max;
        }
        
        //Get data
        $setting_data = Database::select(
            "SELECT setting_id, setting_name, description, setting_value
            FROM settings
            $limit_setting
            ORDER BY setting_id ASC;",
            $parameters
        );
        
        //Construct array
        foreach ($setting_data as $setting) {
            array_push(
                $settings,
                new Setting(
                    $setting['setting_id'],
                    $setting['setting_name'],
                    $setting['description'],
                    $setting['setting_value']
                )
            );
        }
        
        //Return result
        return $settings;
    }
    
    public function getSettingById($id)
    {
        $settings = $this->getSettings(1, 999, $id);
        
        foreach ($settings as $setting) {
            if ($setting->id === $id) {
                return $setting;
            }
        }
        
        return null;
    }
    
    public function update($id, $value)
    {
        $value = Strings::cleanInput($value);
        $validValue = Validator::validateLength('Value', $value, 1, 256);
        
        if ($validValue !== true) {
            return $validValue;
        }
        
        //Update database
        Database::updateWithChangeControl(
            'Modified Settings',
            'settings',
            'setting_id',
            $id,
            "UPDATE settings SET setting_value = ?, modified = NOW()
            WHERE setting_id = ?;",
            [
                $value,
                $id
            ]
        );
        
        return true;
    }
}
