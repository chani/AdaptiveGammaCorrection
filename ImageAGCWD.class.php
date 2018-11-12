<?php
/**
 * Class ImageAGCWD
 *
 * PHP implementation of an Adaptive Gamma Correction with Weighting Distribution
 *
 * Reference:
 *   S. Huang, F. Cheng and Y. Chiu, "Efficient Contrast Enhancement Using Adaptive Gamma Correction With
 *   Weighting Distribution," in IEEE Transactions on Image Processing, vol. 22, no. 3, pp. 1032-1041,
 *   March 2013. doi: 10.1109/TIP.2012.2226047
 *
 * @author Jean-Michel Bruenn <himself@jeanbruenn.info>
 * @copyright 2018 <himself@jeanbruenn.info>
 * @license https://opensource.org/licenses/MIT The MIT License
 * @see https://github.com/chani/AdaptiveGammaCorrection
 * @see https://jeanbruenn.info/2018/11/05/adaptive-gamma-correction-with-weighting-distribution-with-imagick-and-php/
 */
class ImageAGCWD extends ImageAGC
{
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
     * @return \Imagick
     */
    protected function transform()
    {
        // setup HSV working space
        $this->buildHsvWorkingSpace($this->original);

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
        $diffPDF = $maxPDF - $minPDF;

        $pdf_wl_sum = 0;
        foreach ($pdf_l as $intensity => $pdf) {
            $pdf_wl[$intensity] = $maxPDF * pow((($pdf_l[$intensity] - $minPDF) / $diffPDF), $this->alpha);
            $cdf_wl[$intensity] = array_sum(array_filter($pdf_wl, function ($k) use ($intensity) {
                return $k <= $intensity;
            }, ARRAY_FILTER_USE_KEY));
            $pdf_wl_sum += $pdf_wl[$intensity];
        }

        end($pdf_l);
        $lmax = key($pdf_l);

        $imageIterator = $this->t->getPixelIterator();
        foreach ($imageIterator as $pixels) {
            /** @var $pixel \ImagickPixel * */
            foreach ($pixels as $pixel) {
                $c = $pixel->getcolor();
                $l = $c['b'];

                $value = $lmax * pow(($l / $lmax), (1 - ($cdf_wl[$l] / $pdf_wl_sum)));

                $pixel->setColorValue(\Imagick::COLOR_RED, $value / 255);
                $pixel->setColorValue(\Imagick::COLOR_BLUE, $value / 255);
                $pixel->setColorValue(\Imagick::COLOR_GREEN, $value / 255);
            }
            $imageIterator->syncIterator();
        }
        return $this->combine();
    }
}