<?php
/**
 * Class ImageIAGCWD
 *
 * PHP implementation of an improved Adaptive Gamma Correction with Weighting Distribution for
 * brightness distorted images
 *
 * Reference:
 * - Cao, Gang & Huang, Lihui & Tian, Huawei & Huang, Xianglin & Wang, Yongbin & Zhi, Ruicong. (2017).
 *   Contrast Enhancement of Brightness-Distorted Images by Improved Adaptive Gamma Correction.
 *   CAEE. 10.1016/j.compeleceng.2017.09.012.
 * - S. Huang, F. Cheng and Y. Chiu, "Efficient Contrast Enhancement Using Adaptive Gamma Correction With
 *   Weighting Distribution," in IEEE Transactions on Image Processing, vol. 22, no. 3, pp. 1032-1041,
 *   March 2013. doi: 10.1109/TIP.2012.2226047
 *
 * @author Jean-Michel Bruenn <himself@jeanbruenn.info>
 * @copyright 2018 <himself@jeanbruenn.info>
 * @license https://opensource.org/licenses/MIT The MIT License
 * @see https://github.com/chani/AdaptiveGammaCorrection
 * @see https://jeanbruenn.info/2018/11/06/adaptive-gamma-correction-for-brightness-distorted-images-with-imagick-and-php/
 */
class ImageIAGCWD extends ImageAGC
{
    /**
     * @var float
     */
    private $adjustingParameter = 0.5;
    /**
     * @var float
     */
    private $brightAdjustingParameter = 0.25;
    /**
     * @var float
     */
    private $dimmedAdjustingParameter = 0.75;
    /**
     * @var bool
     */
    private $useAGCWD = true;

    /**
     * @return boolean
     */
    public function getUseAGCWD()
    {
        return $this->useAGCWD;
    }

    /**
     * @param boolean $useAGCWD
     */
    public function setUseAGCWD($useAGCWD)
    {
        $this->useAGCWD = $useAGCWD;
    }

    /**
     * @return float
     */
    public function getDimmedAdjustingParameter()
    {
        return $this->dimmedAdjustingParameter;
    }

    /**
     * @param float $dimmedAdjustingParameter
     */
    public function setDimmedAdjustingParameter($dimmedAdjustingParameter)
    {
        $this->dimmedAdjustingParameter = $dimmedAdjustingParameter;
    }

    /**
     * @return float
     */
    public function getBrightAdjustingParameter()
    {
        return $this->brightAdjustingParameter;
    }

    /**
     * @param float $brightAdjustingParameter
     */
    public function setBrightAdjustingParameter($brightAdjustingParameter)
    {
        $this->brightAdjustingParameter = $brightAdjustingParameter;
    }

    /**
     * @return float
     */
    public function getAdjustingParameter()
    {
        return $this->adjustingParameter;
    }

    /**
     * @param float $adjustingParameter
     */
    public function setAdjustingParameter($adjustingParameter)
    {
        $this->adjustingParameter = $adjustingParameter;
    }

    /**
     * @return \Imagick
     */
    protected function transform()
    {
        $pdf_l = [];
        $pdf_wl = [];
        $cdf_wl = [];

        $mn = $this->b->getimagewidth() * $this->b->getimageheight();

        $m_l = 0;
        $imageIterator = $this->b->getPixelIterator();
        foreach ($imageIterator as $pixels) {
            /** @var $pixel \ImagickPixel * */
            foreach ($pixels as $pixel) {
                $c = $pixel->getcolor();
                $m_l += $c['b'] / $mn;
            }
            $imageIterator->syncIterator();
        }

        $t1 = 112;
        $rt = 0.3;
        $t = ($m_l - $t1) / $t1;

        if ($t < ($rt * (-1))) {
            $dimmed = true;
            $bright = false;
        } elseif ($t > $rt) {
            $dimmed = false;
            $bright = true;
        } else {
            // return original if we shouldn't use AGCWD, however, this will still do the colorspace conversation.
            if ($this->useAGCWD === false)
                return $this->combine();
            $dimmed = false;
            $bright = false;
        }

        $alpha = $this->adjustingParameter;
        if ($bright == true) {
            $this->b->negateimage(false, \Imagick::CHANNEL_ALL);
            $this->t = clone $this->b;
            $alpha = $this->brightAdjustingParameter;
        } elseif ($dimmed == true) {
            $alpha = $this->dimmedAdjustingParameter;
        }

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
            $pdf_wl[$intensity] = $maxPDF * pow((($pdf_l[$intensity] - $minPDF) / $diffPDF), $alpha);
            $cdf_wl[$intensity] = array_sum(array_filter($pdf_wl, function ($k) use ($intensity) {
                return $k <= $intensity;
            }, ARRAY_FILTER_USE_KEY));
            $pdf_wl_sum += $pdf_wl[$intensity];
        }

        $r = 0.5;
        $imageIterator = $this->t->getPixelIterator();
        foreach ($imageIterator as $pixels) {
            /** @var $pixel \ImagickPixel * */
            foreach ($pixels as $pixel) {
                end($pdf_l);
                $lmax = key($pdf_l);
                $c = $pixel->getcolor();
                $l = $c['b'];

                $cdf = 1 - ($cdf_wl[$l] / $pdf_wl_sum);
                if ($dimmed == true) {
                    $value = $lmax * pow(($l / $lmax), max($r, $cdf));
                } else {
                    $value = $lmax * pow(($l / $lmax), $cdf);
                }

                $pixel->setColorValue(\Imagick::COLOR_RED, $value / 255);
                $pixel->setColorValue(\Imagick::COLOR_BLUE, $value / 255);
                $pixel->setColorValue(\Imagick::COLOR_GREEN, $value / 255);
            }
            $imageIterator->syncIterator();
        }
        if ($bright == true) {
            $this->t->negateimage(false, \Imagick::CHANNEL_ALL);
        }
        return $this->combine();
    }
}