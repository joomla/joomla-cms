<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <jdoc:include type="head" />
    <!-- Bootstrap core CSS -->
    <link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/custom.css" rel="stylesheet">

    <!-- JQuery -->
    <script src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/jquery.js"></script>

    <!-- Bootstrap JS-->
    <script src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/bootstrap.js"></script>
</head>
<body>
  <!-- Menu Module Position -->
  	<div class="container">
      <p>
        <h4>
          <jdoc:include type="modules" name="menu" style="none" />
        </h4>
      </p>
    </div>
  <!-- End Menu Module -->

  <!-- Showcase Module Position -->
  <?php if ($this->countModules('showcase')) :?>
    <jdoc:include type="modules" name="showcase" style="none" />
  <?php endif ?>
  <!-- End Showcase Module -->

  <div class="container">
    <div class="row pt-4 pb-4">
    <div class=col-4>
      <!-- Block1 Module Position -->
      <?php if ($this->countModules('block1')) :?>
        <jdoc:include type="modules" name="block1" style="none" />
      <?php endif ?>
      <!-- End Block1 Module -->
    </div>
    <div class=col-4>
      <!-- Block2 Module Position -->
      <?php if ($this->countModules('block2')) :?>
        <jdoc:include type="modules" name="block2" style="none" />
      <?php endif ?>
      <!-- End Block2 Module -->   
    </div>
    <div class=col-4>    
      <!-- Block3 Module Position -->
      <?php if ($this->countModules('block3')) :?>
        <jdoc:include type="modules" name="block3" style="none" />
      <?php endif ?>
      <!-- End Block3 Module -->
    </div>
  </div>
  </div>
  <div class="container-fluid p-2 mt-4 mb-4" style="background-color:#eeeeee">
    <div class="container mt-2 mb-2">
    <br>
    <div>
      <jdoc:include type="modules" name="title" style="xhtml" />
    </div>
    <div style="width: 400px;">
      <jdoc:include type="modules" name="login" style="xhtml"/>
    </div>
    </div>
  </div>  
  <footer class="container">
    <div class="">
      <jdoc:include type="modules" name="footer" style="xhtml" />
    </div>
  </footer>
</body>
</html>
