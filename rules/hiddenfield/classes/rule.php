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

namespace registrationrule_hiddenfield;

use MoodleQuickForm;
use stdClass;
use registrationrule_hiddenfield\form\hidden_honeypot_field;
use tool_registrationrules\local\rule\extend_signup_form;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule\rule_trait;

/**
 * Adds a hidden honeypot field to the registration form, a human would not see this
 * field so if it is submitted with data then we must have a bot.
 *
 * @package   registrationrule_hiddenfield
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, post_data_check, extend_signup_form {
    use rule_trait;

    /** @var array Selection of realistic fields to use as a honeypot. */
    private const FIELDNAMES = [
        'contact_phone_alt' => 'Alternative phone number',
        'email_other' => 'Other email address',
        'comment_url' => 'Comment URL',
        'home_address_2' => 'Secondary home address',
        'profile_bio' => 'Profile biography',
        'alt_fax_number' => 'Fax number',
        'social_media' => 'Social media profile',
        'website_url' => 'Website URL',
        'linkedin_profile' => 'LinkedIn profile URL',
        'emergency_contact' => 'Emergency contact name',
        'course_name' => 'Course name',
        'referral_source' => 'Referral source',
        'hobbies' => 'Hobbies',
        'personal_website' => 'Personal website',
    ];

    /** @var stdClass rule plugin instance config. */
    protected stdClass $instanceconfig;

    /**
     * Set rule instance config object.
     *
     * @param stdClass $instanceconfig
     * @return void
     */
    public function set_instance_config(stdClass $instanceconfig): void {
        $this->instanceconfig = $instanceconfig;
    }

    /**
     * Get rule instance config object.
     *
     * @return stdClass
     */
    public function get_instance_config(): stdClass {
        return $this->instanceconfig;
    }

    /**
     * Add a honeypot field to the signup form.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_form(MoodleQuickForm $mform): void {
        global $CFG, $SESSION;

        // Pick a random name and label to use, store this in the session so we know what
        // to check during validation.
        if (!isset($SESSION->registrationrule_hiddenfield_field)) {
            $randkey = array_rand(self::FIELDNAMES);
            $SESSION->registrationrule_hiddenfield_field = [$randkey => self::FIELDNAMES[$randkey]];
        }

        // Register custom field element for use in this form.
        MoodleQuickForm::registerElementType(
            'hidden_honeypot_field',
            "$CFG->dirroot/$CFG->admin/tool/registrationrules/rules/hiddenfield/classes/form/hidden_honeypot_field.php",
            hidden_honeypot_field::class,
        );
        // Add a field using the random details from above.
        $key = array_key_first($SESSION->registrationrule_hiddenfield_field);
        $mform->addElement(
                    'hidden_honeypot_field',
                    'rr_' . $key,
                    $SESSION->registrationrule_hiddenfield_field[$key],
                );
    }

    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     */
    public function post_data_check(array $data): rule_check_result {
        global $SESSION;

        // If the honeypot field is submitted with data then deny registration.
        if ($data['rr_' . array_key_first($SESSION->registrationrule_hiddenfield_field)] != '') {
            return $this->deny(
                score: $this->get_points(),
                feedbackmessage: get_string('failuremessage', 'registrationrule_hiddenfield'),
            );
        }

        return $this->allow();
    }
}
