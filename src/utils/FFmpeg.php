<?php

namespace JoinPhpCommon\utils;

//shell_exec('ffmpeg -i http://ysscw.51ysscw.com/videos/1523965500.mp4 -t 10 -r 1 -f image2 pic.jpeg');
//
//$output = shell_exec('ffprobe -show_format http://ysscw.51ysscw.com/videos/1523965500.mp4');
class FFmpeg
{
    /**
     * 获取视频缩略图(第一帧)
     */
    static function  getVideoThumb($file_path,$thumb_path)
    {
        return shell_exec("ffmpeg -i ".$file_path." -y -f mjpeg -ss 00.001 -t 00.001  ".$thumb_path."    ");
    }

    public $fromVideoType;//需要转换的视频
    public $toVideoType;//转换以后的视频
    public $thumb_name;//截图的图片名
    public $newVideoName;
    public $getVoice;
    public $getVideo;

    /**
     * 视频格式转换
     * @return [type] [description]
     */
    function exVideoType()
    {
        return shell_exec("ffmpeg -i ".$this->fromVideoType." -vn  ".$this->toVideoType."  ");
    }

    //视频的前3秒，重新生成一个新视频
    function createNewVideo()
    {
        return shell_exec("ffmpeg -ss 00:00:01 -t 00:00:30 -y -i ".$this->fromVideoType." -vcodec copy -acodec copy ".$this->newVideoName." ");
    }
    /**
     * 获取视频总长度
     * @return [type] [description]
     */
    function getVideoTime()
    {
        return shell_exec("ffmpeg -i ".$this->fromVideoType." 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//   ");

    }
    /**
     * 获取视频的音频
     * @return [type] [description]
     */
    function getVideoVoice()
    {
        return shell_exec("ffmpeg -i ".$this->fromVideoType." -vn -y -acodec copy ".$this->getVoice." ");
    }
    /**
     * 获取视频的视频    只是画面
     * @return [type] [description]
     */
    function getVideo()
    {
        return shell_exec("ffmpeg -i ".$this->fromVideoType." -vcodec copy -an ".$this->getVideo." ");
    }
    /**
     * 获取视频的信息包括分辨率宽高
     * @return [type] [description]
     */
    function getVideoInfo()
    {

        $command = sprintf('ffmpeg -i "%s" 2>&1', $this->fromVideoType);
        ob_start();
        passthru($command);
        $info = ob_get_contents();
        ob_end_clean();
        $data = array();
        if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
            $data['duration'] = $match[1]; //播放时间
            $arr_duration = explode(':', $match[1]);
            $data['seconds'] = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
            $data['start'] = $match[2]; //开始时间
            $data['bitrate'] = $match[3]; //码率(kb)
        }
        // print_r($info);die;
        if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
            $arr_resolution = explode('x', $match[3]);
            if(count($arr_resolution)==1){
                //Video: h264 (High) (avc1 / 0x31637661), yuv420p(tv, bt709), 544x960, yuv420p(tv, bt709)这里面会有逗号的情况
                preg_match("/Video: (.*?), (.*?,.*?), (.*?)[,\s]/", $info, $match);
                $arr_resolution = explode('x', $match[3]);
                if(count($arr_resolution)!=2){
                    return null;
                }
            }
            $data['vcodec'] = $match[1]; //视频编码格式
            $data['vformat'] = $match[2]; //视频格式
            $data['resolution'] = $match[3]; //视频分辨率
            $data['width'] = $arr_resolution[0];
            $data['height'] = $arr_resolution[1];
        }
        if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
            $data['acodec'] = $match[1]; //音频编码
            $data['asamplerate'] = $match[2]; //音频采样频率
        }
        if (isset($data['seconds']) && isset($data['start'])) {
            $data['play_time'] = $data['seconds'] + $data['start']; //实际播放时间
        }
        // $data['size'] = filesize(str_replace('/', '\\', trim($this->fromVideoType))); //文件大小
        return $data;
    }

}