<?php
/**
 * Class ImageAGCWD
 *
 * PHP implementation of Adaptive Gamma Correction with Weighting Distribution
 *
 * Reference:
 *   S. Huang, F. Cheng and Y. Chiu, "Efficient Contrast Enhancement Using Adaptive Gamma Correction With
 *   Weighting Distribution," in IEEE Transactions on Image Processing, vol. 22, no. 3, pp. 1032-1041,
 *   March 2013. doi: 10.1109/TIP.2012.2226047
 *
 * @author Jean-Michel Bruenn <himself@jeanbruenn.info>
 * @copyright 2018 <himself@jeanbruenn.info>
 * @license https://opensource.org/licenses/MIT The MIT License
 * @see https://github.com/chani/agcwd
 * @todo convert back to original colorspace ^^
 */
class ImageAGCWD
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
     * @var float
     */
    private $alpha = 0.5;

    /**
     * @param float $alpha
     */
    public function setAdjustingParameter($alpha = 0.5)
    {
        $this->alpha = $alpha;
    }

    /**
     * @return float
     */
    public function getAdjustingParameter()
    {
        return $this->alpha;
    }

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
        $pdf_l = [];
        $pdf_wl = [];
        $cdf_wl = [];

        $mn = $this->b->getimagewidth() * $this->b->getimageheight();
        $hist = $this->b->getImageHistogram();

        /** @var $pixel \ImagickPixel * */
        foreach ($hist as $pixel) {
            $color = $pixel->getColor();
            $pdf_l[$color['b']] = ($pixel->getColorCount() / $mn);
        }

        ksort($pdf_l);

        $minPDF = min($pdf_l);
        $maxPDF = max($pdf_l);
        $pdf_wl_sum = 0;
        foreach ($pdf_l as $intensity => $pdf) {
            $pdf_wl[$intensity] = $maxPDF * pow((($pdf_l[$intensity] - $minPDF) / ($maxPDF - $minPDF)), $this->alpha);
            $pdf_wl_sum += $pdf_wl[$intensity];
        }
        foreach ($pdf_wl as $intensity => $pdfw) {
            $cdf_wl[$intensity] = array_sum(array_filter($pdf_wl, function ($k) use ($intensity) {
                    return $k <= $intensity;
                }, ARRAY_FILTER_USE_KEY)) / $pdf_wl_sum;
        }

        $imageIterator = $this->t->getPixelIterator();
        foreach ($imageIterator as $pixels) {
            /** @var $pixel \ImagickPixel * */
            foreach ($pixels as $pixel) {
                end($pdf_l);
                $lmax = key($pdf_l);
                $c = $pixel->getcolor();
                $l = $c['b'];

                $value = $lmax * pow(($l / $lmax), (1 - $cdf_wl[$l]));
                /**
                 * I'm not sure if 255 or 256. Also I'm not sure why 25[5|6] works even for 16bit
                 * images. I believe this should be 65535 for 16bit images?
                 * @todo fix me
                 */
                $pixel->setColorValue(\Imagick::COLOR_RED, $value / 256);
                $pixel->setColorValue(\Imagick::COLOR_BLUE, $value / 256);
                $pixel->setColorValue(\Imagick::COLOR_GREEN, $value / 256);
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
