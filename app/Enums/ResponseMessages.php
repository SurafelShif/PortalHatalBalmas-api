<?php

namespace App\Enums;

enum ResponseMessages: string
{
    const SUCCESS_ACTION = 'הפעולה התבצעה בהצלחה';
    const ERROR_OCCURRED = 'אירעה שגיאה';
    const NO_ROLE_ASSIGNED = 'לא הוקצה תפקיד למשתמש';
    const UNAUTHORIZED = 'המשתמש לא מחובר';
    const USER_NOT_FOUND = 'המשתמש לא נמצא';
    const USERS_NOT_FOUND = 'המשתמשים לא נמצאו';
    const CATEGORY_NOT_FOUND = 'קטגוריה לא נמצאה';
    const POST_NOT_FOUND = 'כתבה לא נמצאה';
    const MISSING_ROLE = 'לא נמצא תפקיד';
    const NOT_ADMIN = 'המשתמש אינו מנהל מערכת';
    const NO_CONTENT = '';
    const ALREADY_ADMIN = 'המשתמש הינו מנהל מערכת';
    const BAD_REQUEST = 'בקשה לא תקינה';
    const SELF_REMOVAL = 'לא ניתן להסיר הרשאה לעצמך';
    const SUCCESS_NO_ACTION_NEEDED = 'לא ניתן לבצע את הפעולה';
    const FORBIDDEN = 'אין לך הרשאות כדי לבצע פעולה זו';
    const HERO_NOT_FOUND = 'הרחט"ל לא נמצא';
    const IMAGE_NOT_FOUND = 'התמונה לא נמצאה';
    const TOO_MANY_REQUESTS = 'יותר מדי בקשות נשלחו, נסה שוב בעוד מספר רגעים';
}
