<?php
/**
 * Class ImageAGC
 *
 * Just converts to HSB and separates the channels so that one can work on the V- (or rather B) channel
 * to transform an image using adaptive gamma correction techniques. This class also holds methods
 * which are identical in the concrete classes.
 *
 * @author Jean-Michel Bruenn <himself@jeanbruenn.info>
 * @copyright 2018 <himself@jeanbruenn.info>
 * @license https://opensource.org/licenses/MIT The MIT License
 * @see https://github.com/chani/AdaptiveGammaCorrection
 */
abstract class ImageAGC
{
    /**
     * @var \Imagick
     */
    protected $im = null;
    /**
     * @var \Imagick
     */
    protected $h = null;
    /**
     * @var \Imagick
     */
    protected $s = null;
    /**
     * @var \Imagick
     */
    protected $b = null;
    /**
     * @var \Imagick
     */
    protected $t = null;
    /**
     * @var int|null
     */
    protected $colorspace = null;

    /**
     * @param \Imagick $im
     */
    public function __construct(\Imagick $im)
    {
        $colorspace = $im->getImageColorspace();
        if ($colorspace == \Imagick::COLORSPACE_UNDEFINED) {
            $colorspace = \Imagick::COLORSPACE_SRGB;
        }
        $this->colorspace = $colorspace;

        if ($colorspace != \Imagick::COLORSPACE_GRAY && $colorspace != \Imagick::COLORSPACE_HSB) {
            $im->transformImageColorspace(\Imagick::COLORSPACE_HSB);

            $h = clone $im;
            $s = clone $im;
            $h->separateImageChannel(\Imagick::CHANNEL_RED);
            $s->separateImageChannel(\Imagick::CHANNEL_GREEN);
            $this->h = $h;
            $this->s = $s;
        }

        $this->im = $im;

        $b = clone $im;
        $b->separateImageChannel(\Imagick::CHANNEL_BLUE);
        $this->b = $b;
        $this->t = clone $b;
    }

    /**
     * @param null $filename
     * @return bool
     */
    public function writeImage($filename = null)
    {
        return $this->transform()->writeimage($filename);
    }

    /**
     * @return Imagick
     */
    protected function transform()
    {
        return $this->combine();
    }

    /**
     * @return \Imagick
     */
    protected function combine()
    {
        if ($this->colorspace == \Imagick::COLORSPACE_GRAY) {
            return $this->t;
        } else {
            $n = new Imagick();
            $n->addImage($this->h);
            $n->addImage($this->s);
            $n->addImage($this->t);
            $n->setimagecolorspace(\imagick::COLORSPACE_HSB);
            $n->mergeimagelayers(\Imagick::LAYERMETHOD_FLATTEN);
            $n = $n->combineImages(\imagick::CHANNEL_ALL);
            $n->setimagecolorspace(\imagick::COLORSPACE_HSB);
        }

        $n->transformimagecolorspace($this->colorspace);

        return $n;
    }

    public function __toString()
    {
        return $this->transform()->__toString();
    }
}