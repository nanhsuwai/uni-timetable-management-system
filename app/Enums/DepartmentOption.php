<?php

namespace App\Enums;

enum DepartmentOption: string
{
    case ITSM = 'Department of Information Technology Supporting and Maintenance';
    case FCST = 'Faculty of Computer System and Technology';
    case FCS = 'Faculty of Computer Science';
    case IS = 'Faculty of Information Science';
    case Physics = 'Natural Science Department';
    case Mathematics = 'Faculty of Computing';
    case English = 'Language Department(English)';
    case Myanmar = 'Language Department(Myanmar)';
}