<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 2015-11-09
 * Time: 19:05
 */

namespace CityHunter;


use Exception;

class Notification
{

    private $type;
    private $message;

    function __construct($message)
    {
        $this->message = $message;
    }

    public function isInfo()
    {
        $this->type = "INFO";
        return $this;
    }

    public function isWarning()
    {
        $this->type = "WARNING";
        return $this;
    }

    public function isError()
    {
        $this->type = "ERROR";
        return $this;
    }

    public function isSuccess()
    {
        $this->type = "SUCCESS";
        return $this;
    }

    public function getIcon()
    {
        switch ($this->type) {
            case "WARNING":
                return "fa-warning";
            case "ERROR":
                return "fa-times-circle";
            case "SUCCESS":
                return "fa-check-circle";
            default:
                return "fa-info-circle";
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getClass()
    {
        return strtolower($this->type);
    }

    public function __toString()
    {
        return "
        <li class=\"" . $this->getClass() . "\">
            <div class=\"notification-icon\" style=\"margin-top: 9px;\">
                <i class=\"fa " . $this->getIcon() . "\"></i>
            </div>
            <div class=\"notification-content\">" . $this->message . "</div>
        </li>";
    }
}