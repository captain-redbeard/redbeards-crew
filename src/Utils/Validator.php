<?php
/**
 * @author captain-redbeard
 * @since 16/03/17
 */
namespace Redbeard\Crew\Utils;

class Validator
{
    /**
    * Validate the specified variable min and max length.
    *
    * @param $name          - name of the variable to be used in the error
    * @param $variable      - variable to check length of
    * @param $min_length    - minimum length
    * @param $max_length    - maximum length
    *
    * @returns string error or 0 if valid
    */
    public static function validateLength($name, $variable, $min_length, $max_length)
    {
        if (strlen(trim($variable)) < $min_length) {
            return $name . ' must be at least ' . $min_length .' character.';
        }
        
        if (strlen($variable) > $max_length) {
            return $name . ' must be less than ' . $max_length . ' characters.';
        }
        
        return 0;
    }
    
    /**
    * Validate the specified email.
    *
    * @param $email - email address to validate
    *
    * @returns string error or 0 if valid
    */
    public static function validateEmail($email)
    {
        $validEmail = self::validateVariable('Email', $email, 4, 256);
        
        if ($validEmail !== 0) {
            return $validEmail;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address.';
        }
        
        return 0;
    }
}
