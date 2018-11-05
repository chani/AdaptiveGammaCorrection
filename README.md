# Adaptive Gamma Correction

These are just some implementations of proposed methods in papers I've read which I did wrote while searching something which might be used to automatically enhance contrast / add gamma-correction to my photos. I've used \Imagick in PHP for that purpose. I'm mostly working on the CLI with the scripts.  

1. AGCWD Adaptive Gamma Correction with Weighting Distribution
2. IAGCWD Adaptive Gamma Correction with Weighting Distribution modified to enhance brightness-distorted pictures

## Example usage

```
// agcwd
$t = new ImageAGCWD(new \Imagick('your-picture.png'));
$t->writeimage('output.png');

// iagcwd
$t = new ImageIAGCWD(new \Imagick('your-picture.png'));
$t->writeimage('output.png');
```

## Results

* AGCWD: https://jeanbruenn.info/2018/11/05/adaptive-gamma-correction-with-weighting-distribution-with-imagick-and-php/
* IAGCWD: https://jeanbruenn.info/2018/11/06/adaptive-gamma-correction-for-brightness-distorted-images-with-imagick-and-php/

## Notes

* currently exports to sRGB colorspace regardless of the original colorspace
* you can set the adjustment parameter using ->setAdjustmentParameter(0.75), default is 0.5
* iagcwd has hardcoded adjustment parameter currently (0.25 for bright images, 0.75 for dimmed)

## References

* Cao, Gang & Huang, Lihui & Tian, Huawei & Huang, Xianglin & Wang, Yongbin & Zhi, Ruicong. (2017). Contrast Enhancement of Brightness-Distorted Images by Improved Adaptive Gamma Correction. CAEE. 10.1016/j.compeleceng.2017.09.012.
* S. Huang, F. Cheng and Y. Chiu, „Efficient Contrast Enhancement Using Adaptive Gamma Correction With Weighting Distribution,“ in IEEE Transactions on Image Processing, vol. 22, no. 3, pp. 1032-1041, March 2013. doi: 10.1109/TIP.2012.2226047
