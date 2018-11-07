<?php
/**
 * Class ImageAGC
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
     * @param \Imagick $im
     */
    public function __construct(\Imagick $im)
    {
        $im->transformImageColorspace(\Imagick::COLORSPACE_HSB);

        $h = clone $im;
        $s = clone $im;
        $b = clone $im;

        $h->separateImageChannel(\Imagick::CHANNEL_RED);
        $s->separateImageChannel(\Imagick::CHANNEL_GREEN);
        $b->separateImageChannel(\Imagick::CHANNEL_BLUE);

        $this->im = $im;
        $this->h = $h;
        $this->s = $s;
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
        $n = new Imagick();
        $n->addImage($this->h);
        $n->addImage($this->s);
        $n->addImage($this->t);
        $n->setimagecolorspace(\imagick::COLORSPACE_HSB);
        $n->mergeimagelayers(\Imagick::LAYERMETHOD_FLATTEN);
        $n = $n->combineImages(\imagick::CHANNEL_ALL);
        $n->setimagecolorspace(\imagick::COLORSPACE_HSB);

        $n->transformimagecolorspace(\Imagick::COLORSPACE_SRGB);

        return $n;
    }

    public function __toString()
    {
        return $this->transform()->__toString();
    }
}