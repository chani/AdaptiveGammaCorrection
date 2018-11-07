# Adaptive Gamma Correction

These are just some implementations of proposed methods in papers I've read which I did wrote while searching something which might be used to automatically enhance contrast / add gamma-correction to my photos. I've used \Imagick in PHP for that purpose. I'm mostly working on the CLI with the scripts.  

1. AGCWD Adaptive Gamma Correction with Weighting Distribution
2. IAGCWD Adaptive Gamma Correction with Weighting Distribution modified to enhance brightness-distorted pictures
3. AGC An adaptive gamma correction for image enhancement

## Example usage

```
$testImage = new Imagick('test.png');

$agcie = new ImageAGCWD($testImage);
$agcie->writeImage('agcie.png');

$agcwd = new ImageAGCWD($testImage);
$agcwd->writeImage('agcwd.png');

$iagcwd = new ImageIAGCWD($testImage);
$iagcwd->writeImage('iagcwd.png');
```

## Results

* AGCWD: https://jeanbruenn.info/2018/11/05/adaptive-gamma-correction-with-weighting-distribution-with-imagick-and-php/
* IAGCWD: https://jeanbruenn.info/2018/11/06/adaptive-gamma-correction-for-brightness-distorted-images-with-imagick-and-php/
* AGC: https://jeanbruenn.info/2018/11/06/another-adaptive-gamma-correction-implementation/

## Notes

* currently exports to sRGB colorspace regardless of the original colorspace
* (agcwd) you can set the adjustment parameter using ->setAdjustmentParameter(0.75), default is 0.5
* iagcwd has hardcoded adjustment parameter currently (0.25 for bright images, 0.75 for dimmed)

## References

* Cao, Gang & Huang, Lihui & Tian, Huawei & Huang, Xianglin & Wang, Yongbin & Zhi, Ruicong. (2017). Contrast Enhancement of Brightness-Distorted Images by Improved Adaptive Gamma Correction. CAEE. 10.1016/j.compeleceng.2017.09.012.
* S. Huang, F. Cheng and Y. Chiu, „Efficient Contrast Enhancement Using Adaptive Gamma Correction With Weighting Distribution,“ in IEEE Transactions on Image Processing, vol. 22, no. 3, pp. 1032-1041, March 2013. doi: 10.1109/TIP.2012.2226047
* Rahman, Shanto & Rahman, Md. Mostafijur & Abdullah-Al-Wadud, M & Al-Quaderi, Golam Dastegir & Shoyaib, Mohammad. (2016). An adaptive gamma correction for image enhancement. EURASIP Journal on Image and Video Processing. 35. 10.1186/s13640-016-0138-1.
