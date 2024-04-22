<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Certificate</title>
    @include('pdfs.certificates.certificate-css')
  </head>

  <body>
    <div class="background-texture">
      <div class="logo-container margin-center">
        <img class="logo" src="{{ $logoImage }}" alt="Hijaz World" />
      </div>

      <div class="text-align-center header">
        <h1 class="header">Certificate <span class="of-font">of  </span> Completion</h1>
      </div>

      <div class="text-align-center">
        <p> This certifies that </p>
        <p class="name">{{ $userProfile->first_name }} {{ $userProfile->last_name }}</p>
        <hr class="width-600"/>
        <p class="small-line-height"> has successfully completed</p>
        <p class="course-name"><b>{{ $entityName }}</b></p>
        <p class="small-line-height">on</p>
        <p class="date">{{ $certificateCreatedAt }}</p>
        <hr class="width-300"/>
      </div>

      <div class="footer">
        <div class="third">
          <img class="signature" src="{{ $signatureImage }}" />
          <hr class="signature-hr footer-hr">
          Name
        </div>

        <div class="third date-in-img-margin">
          <img class="date-img" src="{{ $dateImage }}}" alt="Hijaz World" />
          <span class="date-in-img">{{ $year }}</span>
        </div>

        <div class="third">
          <span>{{ $certificateDateWithMonth }}</span>
          <hr class="footer-hr">
          Date
        </div>
      </div>
    </div>
  </body>
</html>
