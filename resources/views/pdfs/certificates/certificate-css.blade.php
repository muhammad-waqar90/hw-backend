<style>
@font-face {
  font-family: Cinzel;
  src: url("{{ storage_path('pdfs/fonts/cinzel.ttf') }}") format("truetype");
  font-weight: 400;
  font-style: normal;
}
@font-face {
  font-family: Edwardian;
  src: url("{{ storage_path('pdfs/fonts/Edwardian Script ITC.ttf') }}") format("truetype");
  font-weight: 400;
  font-style: normal;
}
@font-face {
  font-family: Montserrat;
  src: url("{{ storage_path('pdfs/fonts/Montserrat-Medium.ttf') }}") format("truetype");
  font-weight: 400;
  font-style: normal;
}
@font-face {
  font-family: Montserrat;
  src: url("{{ storage_path('pdfs/fonts/Montserrat-SemiBold.ttf') }}") format("truetype");
  font-weight: 400;
  font-style: bold;
}
@page {
  size: 600pt  842pt landscape; 
  margin:0;padding:0;
}
body{
  padding: 14px 14px 0 14px;
  color: #fff;
  font-family: Montserrat;
}
hr{
  border: 1px solid #fff;
}
.background-texture {
  background: #2961AA;
  background-image: url("{{resource_path('views/pdfs/certificates/img/white-texture.png')}}");
  width: 100vw;
  height: 760px;
}
.logo {
  width: 200px;
  padding: 0 25px;
}
.logo-container {
  background-color: #fff;
  border: 1px solid transparent;
  border-radius: 0px 0px 20px 20px;
  width: 250px;
}
.date-img {
  width: 250px;
}
.date-in-img{
  position: absolute;
  top: 20px;
  left: 163px;
}
.header{
  font-family: Cinzel;
  font-weight: 400;
  font-size: 50px;
  margin-bottom: 0;
}
.of-font{
  font-family: Edwardian;
  font-weight: 400;
}
.name{
  margin: 0;
  font-size: 28px;
}
.course-name{
  margin: 0;
  font-size: 20px;
}
.small-line-height{
  line-height: 8px;
}
.date{
  font-size: 20px;
  margin-bottom: 0;
}
.margin-center{
  margin: 0 auto;
}
.text-align-center{
  text-align: center;
}
.width-600{
  width: 600px;
}
.width-300{
  width: 300px;
}
.signature {
  width: 350px;
  top: -82px;
  position: absolute;
}
.footer{
  width: 100%;
  height: 60px;
  position: absolute;
  bottom: 60px
}
.third{
  width: 33%;
  float: left;
  text-align: center;
}
.footer-hr{
  width: 80%;
}
.signature-hr{
  margin-top: 35px;
}
.date-in-img-margin {
  margin-top: 15px;
}

</style>