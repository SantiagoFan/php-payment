<?php
namespace JoinPhpCommon\example\utils;
use JoinPhpCommon\utils\FFmpeg;

class FFmpegTest
{
    function index(){
        // 获取视频第一帧做封面
        $video_Path ='1.mp4';
        $thumb_Path = '1.jpg';
        FFmpeg::getVideoThumb( $video_Path, $thumb_Path);
    }
}