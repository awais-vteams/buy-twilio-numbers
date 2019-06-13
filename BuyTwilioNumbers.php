<?php
// Get the PHP helper library from https://twilio.com/docs/libraries/php

require_once '/path/to/vendor/autoload.php'; // Loads the library

use Twilio\Rest\Client;

/**
 * Class BuyTwilioNumbers
 * Buy bulk random US Twilio numbers
 *
 * Usage Example
 *
 * ```php
 *      new BuyTwilioNumbers(100);
 * ```
 */
class BuyTwilioNumbers
{
    /**
     * @var Client
     */
    private $client;

    const COUNTRY = 'US';

    /**
     * From Twilio max available number in one request
     */
    const MAX_PER_PAGE = 30;

    /**
     * BuyTwilioNumbers constructor.
     *
     * @param int $nosToPurchase sd
     *
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function __construct($nosToPurchase = 1)
    {
        // Your Account Sid and Auth Token from twilio.com/user/account
        $sid = "Account_Sid";
        $token = "Auth_Token";

        $this->client = new Client($sid, $token);

        $this->_startBuying($nosToPurchase);
    }

    /**
     * Start buying
     *
     * @param int $nosToBuy Nos to buy
     *
     * @return void
     */
    private function _startBuying($nosToBuy)
    {
        $pages = ceil($nosToBuy / self::MAX_PER_PAGE);
        $done = 0;

        for ($i = 1; $i <= $pages; $i++) {
            $twilioPhoneNumbers = $this->_getNumbers();

            if (($i * self::MAX_PER_PAGE) <= $nosToBuy) {
                $to = self::MAX_PER_PAGE;
            } else {
                $to = $nosToBuy - (($i - 1) * self::MAX_PER_PAGE);
            }

            for ($loop = 0; $loop < $to; $loop++) {
                $this->_buy($twilioPhoneNumbers[$loop]->phoneNumber, $done);
            }
        }

        if ($done < $nosToBuy) {
            $newCount = $nosToBuy - $done;
            $this->_startBuying($newCount);
        }
    }

    /**
     * See the doc
     * https://www.twilio.com/docs/phone-numbers/api/available-phone-numbers#local
     *
     * @return array
     */
    private function _getNumbers()
    {
        return $this->client->availablePhoneNumbers(self::COUNTRY)->local->read();
    }

    /**
     * Purchase from Twilio API
     *
     * @param string $twilioPhoneNumber
     * @param int $done
     *
     * @return void
     */
    private function _buy($twilioPhoneNumber, &$done)
    {
        try {
            $this->client->incomingPhoneNumbers->create(
                ['phoneNumber' => $twilioPhoneNumber]
            );
            echo "Bought successful: {$twilioPhoneNumber}";

            $done++;
        } catch (Exception $exception) {
            echo 'ERROR!' . $exception->getMessage();
        }
    }
}
