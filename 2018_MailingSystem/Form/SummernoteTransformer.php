<?php
namespace Phinedo\OutilsBundle\Form;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SummernoteTransformer implements DataTransformerInterface
{
    /**
     * Transform a input text to a viewable text.
     *
     * @param  string $rawData
     * @return string
     * @throws TransformationFailedException if string (rawData) is not found.
     */
    public function transform($rawData)
    {
        return $rawData;
    }
    
    /**
     * Transform a viewable text to a input text.
     *
     * @param  string $rawData
     * @return string
     * @throws TransformationFailedException if string (rawData) is not found.
     */
    public function reverseTransform($rawData)
    {
        //On commence par les gifs:
        $pattern = '/::([A-Za-z0-9_-]{1,30})_gif::/';
        $rawData = preg_replace_callback($pattern, function($matches){
                    if(file_exists(ROOTDIR.'/web/images/smileys/'.$matches[1].'.gif')){
                    return ' <span style="background: url(\'../../../../../../../../images/smileys/'.$matches[1].'.gif\'); display: inline-block; width: 30px; height: 30px; background-size: 30px;"></span> ';
                    }
                    return $matches[0];
                }, $rawData);
        //Puis les png
        $pattern = '/::([A-Za-z0-9_-]{1,30})::/';
        $rawData = preg_replace_callback($pattern, function($matches){
                    if(file_exists(ROOTDIR.'/web/images/smileys/'.$matches[1].'.png')){
                    return ' <span style="background: url(\'../../../../../../../../images/smileys/'.$matches[1].'.png\'); display: inline-block; width: 30px; height: 30px; background-size: 30px;"></span> ';
                    }
                    return $matches[1];
                }, $rawData);
        $rawData = strip_tags($rawData, '<blockquote><ol><p><li><ul><div><img><span><br><table><tbody><tr><td><th><hr><h1><h2><h3><h4><h5><h6><h7><a>');
        return $rawData;
    }
}