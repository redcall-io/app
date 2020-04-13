<?php

namespace Bundles\TwilioBundle\Event;

use Bundles\TwilioBundle\Entity\TwilioCall;
use Twilio\TwiML\VoiceResponse;

class TwilioCallEvent
{
    /**
     * @var TwilioCall
     */
    private $call;

    /**
     * @var int|null
     */
    private $keyPressed;

    /**
     * @var VoiceResponse|null
     */
    private $response;

    public function __construct(TwilioCall $call, int $keyPressed = null)
    {
        $this->call = $call;
        $this->keyPressed = $keyPressed;
    }

    public function getCall(): TwilioCall
    {
        return $this->call;
    }

    public function getKeyPressed(): ?int
    {
        return $this->keyPressed;
    }

    public function getResponse(): ?VoiceResponse
    {
        return $this->response;
    }

    public function setResponse(VoiceResponse $response): TwilioCallEvent
    {
        $this->response = $response;

        return $this;
    }
}