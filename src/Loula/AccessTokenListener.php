<?php
/**
 * Date: 15/11/02
 * Time: 18:39
 */

namespace Loula;


interface AccessTokenListener
{
    public function changedAccessTokenAt($accessToken, $refreshToken);
}