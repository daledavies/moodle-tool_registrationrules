<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Reference implementation of a registration rule subplugin.
 *
 * @package    registrationrule
 * @subpackage nope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace registrationrule_hcaptcha;

use \tool_registrationrules\local\rule_check_result;


class rule extends \tool_registrationrules\local\rule\rule_base {
    public $config = [];
    
    const SETTINGS_FIELDS = ['hcaptcha_sitekey', 'hcaptcha_secret'];
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public static function extend_settings_form ($mform): void {
        $mform->addElement('text', 'hcaptcha_sitekey', get_string('sitekey', 'registrationrule_hcaptcha'));
        $mform->addRule('hcaptcha_sitekey', get_string('required'), 'required');
        
        $mform->addElement('text', 'hcaptcha_secret', get_string('secret', 'registrationrule_hcaptcha'));
        $mform->addRule('hcaptcha_secret', get_string('required'), 'required');
    }
    
    public function extend_form($mform): void {

        // This is the basic JS for hCaptcha.
        $html = '<script src="https://js.hcaptcha.com/1/api.js" async defer></script>';
        
        // But we also need to add the HTML for the result.
        $html .= '<div class="h-captcha" data-sitekey="' . htmlspecialchars($this->config->hcaptcha_sitekey) . '"></div>';
        
        $mform->addElement('hidden', 'h-captcha-response', '');
        $mform->addElement('html', $html);
    }
    
    public function post_data_check($data): rule_check_result  {        
        // Build the data used for validation.
        $validationpost = [
            'secret' => $this->config->hcaptcha_secret,
            'sitekey' =>  $this->config->hcaptcha_sitekey,
            'response' => $data['h-captcha-response'],
            ];
            
        
        // Call the hCaptcha API for validation.
        $ch = curl_init('https://api.hcaptcha.com/siteverify'); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $validationpost);
        
        // Get and decode response.
        $response = json_decode(curl_exec($ch));
        $error = curl_error($ch);
        curl_close($ch);
        
        // If empty or false the captcha failed and the result is negative.
        $result = !empty($response->success);
        
        return new rule_check_result($result, get_string('resultmessage', 'registrationrule_hcaptcha'));
    }
    
    public function pre_data_check(): ?rule_check_result { return null; }
}

