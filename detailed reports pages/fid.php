<?php
  session_start();
  if (!isset($_SESSION["basic_auth"]) && !isset($_SESSION["admin_auth"])) {
    header("Location: ../login.php");
    exit();
  }
  if (isset($_SESSION["basic_auth"]) && $_SESSION["basic_auth"] != true) {
    header("Location: ../login.php");
    exit();
  }
  if (isset($_SESSION["admin_auth"]) && $_SESSION["admin_auth"] != true) {
    header("Location: ../login.php");
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FID Report</title>

  <link rel="stylesheet" href="reports.css">

  <script src="https://cdn.zingchart.com/zingchart.min.js"></script>
  <script src="https://cdn.zinggrid.com/zinggrid.min.js" defer></script>
</head>

<body>
  <nav style="text-align:right; font-size:20px">
    <a href="../index.php">Home</a>
    <a href="../logout.php">Logout</a>
  </nav>
  <h1>FID Report (see note on the bottom about this metric)</h1>

  <script>
    <?php 
      // connect
      $mysqli = new mysqli("localhost", "root", "Carbon742!@#", "hw3");
      if (mysqli_connect_errno()) {
          printf("Connect failed: %s\n", mysqli_connect_error());
          exit();
      }
      // initialize arrays
      $fidGrid = [];
      $fidCount = 0;
      if ($result = $mysqli->query("SELECT * FROM fid")) {
        $fidGrid = $result->fetch_all(MYSQLI_ASSOC);
        $fidCount = $result->num_rows;
        $result->close();
      }
      $fid = mysqli_query($mysqli, "SELECT * FROM fid");
    ?>
    // for zinggrid
    var fidGridValues = <?php echo json_encode($fidGrid) ?>;

    // for zingchart
    var fidData = [<?php
      while($info=mysqli_fetch_array($fid))
      // select vitals column
      echo '\''.$info['vitalsScore'].'\''.',';
      ?>];
    var fidGood = 0;
    var fidNeedsImprovement = 0;
    var fidPoor = 0;
    for (let i = 0; i < fidData.length; i++) {
      if (fidData[i] == 'good') {
        fidGood++;
      }
      else if (fidData[i] == 'poor') {
        fidPoor++;
      }
      else {
        fidNeedsImprovement++;
      }
    }

    // for dynamic report
    var goodPercent = fidGood/(fidGood+fidNeedsImprovement+fidPoor) * 100;
    var impPercent = fidNeedsImprovement/(fidGood+fidNeedsImprovement+fidPoor) * 100;
    var poorPercent = fidPoor/(fidGood+fidNeedsImprovement+fidPoor) * 100;
    goodPercent = goodPercent.toFixed(2);
    impPercent = impPercent.toFixed(2);
    poorPercent = poorPercent.toFixed(2);

    // to calculate averages, query from db again and select data column
    <?php 
        $fid = mysqli_query($mysqli, "SELECT * FROM fid");
    ?>
    var fidVals = [<?php
        while($info=mysqli_fetch_array($fid))
        echo $info['data'].',';
        ?>];
    var fidAvg = 0;
    for (let i = 0; i < fidVals.length; i++) {
        fidAvg += fidVals[i];
    }
    fidAvg = fidAvg/fidVals.length;
    fidAvg = fidAvg.toFixed(2);

    <?php
      $mysqli->close();
    ?>
  </script>
  
    <zing-grid id="fidGrid"
        editor contextmenu
        caption="FID Data From Visitors"
        sort
        search
        pager
        page-size="5"
        layout="row"
        viewport-stop
        >
    </zing-grid>

  <figure id="fid_pie"></figure>

  <h2>How is FID performance for users?</h2>
  <article>
    <p>
        First Input Delay (FID) measures interactivity and is one of 
        three important web vitals that apply to all web pages. It reports the 
        time from when a user first interacts with a page (i.e. when they click 
        a link, tap on a button, or use a custom, JavaScript-powered control) 
        to the time when the browser is actually able to begin processing event 
        handlers in response to that interaction. 
        <br />
        To provide good user experience, pages should have an FID of less than 
        0.1 second. Improvement is needed if FID occurs between 0.1 to 0.3 
        second, and is considered poor if it occurs after 0.3 second.
    </p>
    <p>
        Out of the <?php echo $fidCount ?> visits to the main carbonchen.site page,
        <ul>
            <li>
                <span id="good"></span> experience good FID,
            </li>
            <li>
                <span id="imp"></span> experience "needs improvement" FID,
            </li>
            <li>
                <span id="poor"></span> experience poor FID.
            </li>
        </ul>
    </p>
    <p>
        The average FID among all users is <span id="avg"></span> seconds.
    </p>
  </article>

  <br />
  <h2>NOTE</h2>
  <p>
      The collector script seems to collect this metric twice per page reload.
      The first collection should be the accurate FID, while the second collection 
      usually has a wildly larger value than the first. Because of this, the data 
      from this report is not very accurate. However, if the collector script was
      improved to properly collect FID, this report would be accurate since it is
      dynamically generated. 
  </p>

  <script>
    document.querySelector('#fidGrid').data = fidGridValues;
    document.querySelector('#good').innerText = `${fidGood} (${goodPercent})%`;
    document.querySelector('#imp').innerText = `${fidNeedsImprovement} (${impPercent})%`;
    document.querySelector('#poor').innerText = `${fidPoor} (${poorPercent})%`;
    document.querySelector('#avg').innerText = `${fidAvg}`;

    zingchart.render({
      id: "fid_pie",
      data: {
        type: 'pie',
        title: {
          text: "First Input Delay (FID)",
          'font-size':25
        },
        legend: {
          x: "60%",
          y: "20%",
          'draggable': true,
          'border-width': 1,
          'border-color': "gray",
          'border-radius': "5px",
          header: {
            text: "Legend",
            'font-family': "Georgia",
            'font-size':18,
            'font-color': "#3333cc",
            'font-weight': "normal"
          },
          item: {
            'font-size':15,
          },
          marker: {
            type: "circle"
          },
          'toggle-action': "remove",
          'minimize': true,
          'icon': {
            'line-color': "#9999ff"
          },
        },
        plotarea: {
          'margin-right': "15%",
          'margin-top': "10%"
        },
        plot: {
          'value-box': {
            'font-size':20,
            'font-weight': "normal",
            placement: "out"
          }
        },
        series: [
          {
            values: [fidGood],
            text: 'good',
            'background-color': "green"
          },
          {
            values: [fidNeedsImprovement],
            text: 'needs improvement',
            'background-color': "orange"
          },
          {
            values: [fidPoor],
            text: 'poor',
            'background-color': "red"
          }
        ]
      }
    });
  </script>

</body>

</html>

