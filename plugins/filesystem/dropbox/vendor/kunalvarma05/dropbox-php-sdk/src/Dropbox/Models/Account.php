<?php
namespace Kunnu\Dropbox\Models;

class Account extends BaseModel
{
    /**
     * Account ID
     *
     * @var string
     */
    protected $account_id;

    /**
     * User name details
     *
     * @var array
     */
    protected $name = [];

    /**
     * Account Email
     *
     * @var string
     */
    protected $email;

    /**
     * Whether the user has verified their e-mail address
     *
     * @var boolean
     */
    protected $email_verified = false;

    /**
     * Account Profile Pic URL
     *
     * @var string
     */
    protected $profile_photo_url;

    /**
     * Whether the user has been disabled
     *
     * @var boolean
     */
    protected $disabled = false;

    /**
     * User's two-letter country code
     *
     * @var string
     */
    protected $country;

    /**
     * Language of User's account
     *
     * @var string
     */
    protected $locale;

    /**
     * User's referral link
     *
     * @var string
     */
    protected $referral_link;

    /**
     * Indicates whether a work account is linked
     *
     * @var boolean
     */
    protected $is_paired = false;

    /**
     * User's account type
     *
     * @var string
     */
    protected $account_type;

    /**
     * Create a new Account instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->account_id = $this->getDataProperty('account_id');
        $this->name = (array) $this->getDataProperty('name');
        $this->email = $this->getDataProperty('email');
        $this->email_verified = $this->getDataProperty('email_verified');
        $this->disabled = $this->getDataProperty('disabled');
        $this->profile_photo_url = $this->getDataProperty('profile_photo_url');
        $this->locale = $this->getDataProperty('locale');
        $this->country = $this->getDataProperty('country');
        $this->referral_link = $this->getDataProperty('referral_link');
        $this->is_paired = $this->getDataProperty('is_paired');

        //Account Type
        $account_type = $this->getDataProperty('account_type');

        if (is_array($account_type) && !empty($account_type)) {
            $this->account_type = $account_type['.tag'];
        }
    }

    /**
     * Get Account ID
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Get Account User's Name Details
     *
     * @return array
     */
    public function getNameDetails()
    {
        return $this->name;
    }

    /**
     * Get Display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        $name = $this->name;

        if (isset($name['display_name'])) {
            return $name['display_name'];
        }

        return "";
    }

    /**
     * Get Account Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Whether account email is verified
     *
     * @return boolean
     */
    public function emailIsVerified()
    {
        return $this->email_verified ? true : false;
    }

    /**
     * Get Profile Pic URL
     *
     * @return string
     */
    public function getProfilePhotoUrl()
    {
        return $this->profile_photo_url;
    }

    /**
     * Whether acocunt has been disabled
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled ? true : false;
    }

    /**
     * Get User's two-lettered country code
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Get account language
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get user's referral link
     *
     * @return string
     */
    public function getReferralLink()
    {
        return $this->referral_link;
    }

    /**
     * Whether work account is paired
     *
     * @return boolean
     */
    public function isPaired()
    {
        return $this->is_paired ? true : false;
    }

    /**
     * Get Account Type
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->account_type;
    }
}
