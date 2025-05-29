<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title=API_TITLE,
 *     version=API_VERSION,
 *     description=API_DESCRIPTION,
 *     @OA\Contact(
 *         email=CONTACT_EMAIL
 *     ),
 *     @OA\License(
 *         name=LICENSE_NAME,
 *         url=LICENSE_URL
 *     )
 * ),
 * 
 *   @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       in="header",
 *       name="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *    ),
 * ) 
 */
abstract class Controller
{
    //
}
