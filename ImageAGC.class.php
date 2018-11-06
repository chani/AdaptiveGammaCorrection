<?php
/**
 * Class ImageAGC
 *
 * PHP implementation of an Adaptive Gamma Correction
 *
 * Reference:
 * - Rahman, Shanto & Rahman, Md. Mostafijur & Abdullah-Al-Wadud, M & Al-Quaderi, Golam Dastegir &
 *   Shoyaib, Mohammad. (2016). An adaptive gamma correction for image enhancement. EURASIP Journal
 *   on Image and Video Processing. 35. 10.1186/s13640-016-0138-1.
 *
 * @author Jean-Michel Bruenn <himself@jeanbruenn.info>
 * @copyright 2018 <himself@jeanbruenn.info>
 * @license https://opensource.org/licenses/MIT The MIT License
 * @see https://github.com/chani/AdaptiveGammaCorrection
 * @see https://jeanbruenn.info/2018/11/06/another-adaptive-gamma-correction-implementation/
 */
class ImageAGC
{
    /**
     * @var \Imagick
     */
    private $im = null;
    /**
     * @var \Imagick
     */
    private $h = null;
    /**
     * @var \Imagick
     */
    private $s = null;
    /**
     * @var \Imagick
     */
    private $b = null;
    /**
     * @var \Imagick
     */
    private $t = null;

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
     * @return \Imagick
     */
    private function transform()
    {
        $data = $this->b->getImageChannelMean(\Imagick::CHANNEL_ALL);
        if (is_nan($data['standardDeviation'])) {
            echo "Couldn't determine standard Deviation, won't process...";
            return;
        }
        $standardDeviation = $data['standardDeviation'] / $this->b->getQuantum();
        $mean = $data['mean'] / $this->b->getQuantum();

        $r = 3;
        $subClass = ($mean >= 0.5) ? 'b' : 'd';
        $class = (4 * $standardDeviation <= 1 / $r) ? 'lc' . $subClass : 'hc' . $subClass;
        echo $class . "\n";
        $imageIterator = $this->t->getPixelIterator();
        foreach ($imageIterator as $pixels) {
            /** @var $pixel \ImagickPixel * */
            foreach ($pixels as $pixel) {
                $color = $pixel->getcolor();
                /**
                 * I'm not sure if 255 or 256. Also I'm not sure why 25[5|6] works even for 16bit
                 * images. I believe this should be 65535 for 16bit images?
                 * @todo fix me
                 */
                $l = $color['b'] / 255;

                if ($class == 'lcb' || $class == 'lcd') {
                    $y = -log($standardDeviation, 2);
                } else {
                    $y = exp((1 - ($mean + $standardDeviation)) / 2);
                }

                if ($class == 'lcb' || $class == 'hcb') {
                    $value = pow($l, $y);
                } else {
                    $m = pow($mean, $y);
                    $in = pow($l, $y);
                    $value = $in / ($in + ((1 - $in) * $m));
                }

                $pixel->setColorValue(\Imagick::COLOR_RED, $value);
                $pixel->setColorValue(\Imagick::COLOR_BLUE, $value);
                $pixel->setColorValue(\Imagick::COLOR_GREEN, $value);
            }
            $imageIterator->syncIterator();
        }
        return $this->combine();
    }

    /**
     * @return \Imagick
     */
    private function combine()
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

    /**
     * @param null $filename
     * @return bool
     */
    public function writeImage($filename = null)
    {
        return $this->transform()->writeimage($filename);
    }

    public function __toString()
    {
        return $this->transform()->__toString();
    }
}
