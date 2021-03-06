<?php
/* File Info
 * Author: xygengcn
 * CreateTime: 2020/4/12 下午3:27:00
 * LastEditor: xygengcn
 * ModifyTime: 2020/4/14 下午7:26:11
 * Description:
 */

/**
 *Bing每日一图
 *
 */
namespace app\controller;

class Bing
{
    private $url = "https://cn.bing.com/HPImageArchive.aspx?format=js";
    public function index()
    {
        $redis = redis();
        $APIXYGENGCNBING = $redis->get("APIXYGENGCNBING");
        if (isset($APIXYGENGCNBING) && !empty($APIXYGENGCNBING)) {
            $imgurl = $APIXYGENGCNBING;
        } else {
            $str = file_get_contents($this->url . '&idx=0&n=1');
            $str = json_decode($str, true);
            $imgurl = "http://cn.bing.com" . $str["images"][0]["url"];
            $expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
            $redis->setex("APIXYGENGCNBING", $expireTime - timestamp(10), $imgurl);
        }
        if ($imgurl) {
            $opt = new \core\utils\LoadImage();
            $opt->create()->load($imgurl);
        } else {
            error('获取图片错误');
        }
    }
    public function week()
    {
        $redis = redis();
        $lLen = $redis->lLen("APIBINGWEEK");
        if ($lLen != 0) {
            $bingWeek = $redis->lrange('APIBINGWEEK', 0, -1);
        } else {
            for ($i = 0; $i <= 7; $i++) {
                $contents = $this->url . '&idx=' . "" . $i . "" . '&n=1';
                $str = file_get_contents($contents);
                $str = json_decode($str, true);
                $bingWeek[] = 'http://cn.bing.com' . $str["images"][0]["url"];
                $redis->lpush('APIBINGWEEK', 'http://cn.bing.com' . $str["images"][0]["url"]);
            }
            $expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
            $redis->expireAt('APIBINGWEEK', $expireTime);
        }
        response($bingWeek);
    }
}
