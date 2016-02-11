# tpe3d

將台北市開放的 「臺北市自動化3D近似建物模型」 轉換成 GeoJSON 在地圖上顯示

展示: http://sheethub.github.io/tpe3d/3dtaipei4347-2.html

檔案說明
--------
* crawler.php 把完整的檔案全部下載下來，並放到 kmzs/ 資料夾下 (不過為避免 DDoS 台北市政府，建議這程式不需要自行執行，下方有已下載好打包檔放在 Dropbox)
* dae-geojson.php 把 kmzs/ 資料夾下的所有 kmz 中的 dae 檔解出來，並且算成 geojson
* geojsons/ 這邊已經是處理好的所有 geojson


檔案下載
--------
* [data.taipei 資料來源](http://data.taipei/opendata/datalist/datasetMeta?oid=9b7d78d2-0d73-4b42-9b29-c1640efed0eb)
* [Dropbox 打包](https://ronnywang-public.s3.amazonaws.com/opendata/tpe3d/kmzs.zip)

授權方式
--------
* 資料授權方式同台北市開放資料授權
* 程式碼授權採用 BSD License
