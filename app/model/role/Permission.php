<?php

namespace TugasAkhir\model\role;

enum Permission
{
    case UPDATE_OWN_PASSWORD;
    case MANAGE_GRADES;
    case READ_OWN_GRADES;
    case MANAGE_ATTENDANCE;
    case MANAGE_ACCOUNTS;
    case MANAGE_PPM_FORMS;
    case MANAGE_ANNOUNCEMENTS;
    case MANAGE_ADMIN_DOCUMENTS;
    case MANAGE_OWN_DOCUMENTS;
}

