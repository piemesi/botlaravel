<?php
/**
 * Created by PhpStorm.
 * User: malgrat
 * Date: 09.07.17
 * Time: 13:47
 */

namespace App\Services\TT;


interface ITT
{
    public function getChannelAllPosts(int $channelId, bool $unSent = false, bool $sent = false, bool $deleted = false);

    public function getPostByHash(string $hash);

    public function checkChannelsPosts(int $all = 0);

    public function getChannelsByCompanyId(int $companyId);
}