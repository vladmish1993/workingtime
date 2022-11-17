<?php
namespace Interfaces;

interface OpeningHoursInterface
{
    function isOpen(\DateTime $date);
    function nextOpening(\DateTime $date);
}