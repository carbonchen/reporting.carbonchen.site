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
  <title>LCP Report</title>

  <link rel="stylesheet" href="reports.css">

  <script src="https://cdn.zingchart.com/zingchart.min.js"></script>
  <script src="https://cdn.zinggrid.com/zinggrid.min.js" defer></script>
</head>

<body>
  <nav style="text-align:right; font-size:20px">
    <a href="../index.php">Home</a>
    <a href="../logout.php">Logout</a>
  </nav>
  <h1>LCP Report</h1>

  <script>
    <?php 
      // connect
      $mysqli = new mysqli("localhost", "root", "Carbon742!@#", "hw3");
      if (mysqli_connect_errno()) {
          printf("Connect failed: %s\n", mysqli_connect_error());
          exit();
      }
      // initialize arrays
      $lcpGrid = [];
      $lcpCount = 0;
      if ($result = $mysqli->query("SELECT * FROM lcp")) {
        $lcpGrid = $result->fetch_all(MYSQLI_ASSOC);
        $lcpCount = $result->num_rows;
        $result->close();
      }
      $lcp = mysqli_query($mysqli, "SELECT * FROM lcp");
    ?>
    // for zinggrid
    var lcpGridValues = <?php echo json_encode($lcpGrid) ?>;

    // for zingchart
    var lcpData = [<?php
      while($info=mysqli_fetch_array($lcp))
      // select vitals column
      echo '\''.$info['vitalsScore'].'\''.',';
      ?>];
    var lcpGood = 0;
    var lcpNeedsImprovement = 0;
    var lcpPoor = 0;
    for (let i = 0; i < lcpData.length; i++) {
      if (lcpData[i] == 'good') {
        lcpGood++;
      }
      else if (lcpData[i] == 'poor') {
        lcpPoor++;
      }
      else {
        lcpNeedsImprovement++;
      }
    }

    // for dynamic report
    var goodPercent = lcpGood/(lcpGood+lcpNeedsImprovement+lcpPoor) * 100;
    var impPercent = lcpNeedsImprovement/(lcpGood+lcpNeedsImprovement+lcpPoor) * 100;
    var poorPercent = lcpPoor/(lcpGood+lcpNeedsImprovement+lcpPoor) * 100;
    goodPercent = goodPercent.toFixed(2);
    impPercent = impPercent.toFixed(2);
    poorPercent = poorPercent.toFixed(2);

    // to calculate averages, query from db again and select data column
    <?php 
        $lcp = mysqli_query($mysqli, "SELECT * FROM lcp");
    ?>
    var lcpVals = [<?php
        while($info=mysqli_fetch_array($lcp))
        echo $info['data'].',';
        ?>];
    var lcpAvg = 0;
    for (let i = 0; i < lcpVals.length; i++) {
        lcpAvg += lcpVals[i];
    }
    lcpAvg = lcpAvg/lcpVals.length;
    lcpAvg = lcpAvg.toFixed(2);

    <?php
      $mysqli->close();
    ?>
  </script>
  
    <zing-grid id="lcpGrid"
        editor contextmenu
        caption="LCP Data From Visitors"
        sort
        search
        pager
        page-size="5"
        layout="row"
        viewport-stop
        >
    </zing-grid>

  <figure id="lcp_pie"></figure>

  <h2>How is LCP performance for users?</h2>
  <article>
    <p>
        Largest Contentful Paint (LCP) measures load performance and is one of 
        three important web vitals that apply to all web pages. It reports the 
        render time of the largest image or text block visible to the user. 
        <br />
        To provide good user experience, LCP should occur within the first 2.5 
        seconds of the page starting to load. Improvement is needed if LCP occurs 
        between 2.5 to 4 seconds, and is considered poor if it occurs after 4 
        seconds.
    </p>
    <p>
        Out of the <?php echo $lcpCount ?> visits to the main carbonchen.site page,
        <ul>
            <li>
                <span id="good"></span> experience good LCP,
            </li>
            <li>
                <span id="imp"></span> experience "needs improvement" LCP,
            </li>
            <li>
                <span id="poor"></span> experience poor LCP.
            </li>
        </ul>
    </p>
    <p>
        The average LCP among all users is <span id="avg"></span> seconds.
    </p>
  </article>

  <script>
    document.querySelector('#lcpGrid').data = lcpGridValues;
    document.querySelector('#good').innerText = `${lcpGood} (${goodPercent})%`;
    document.querySelector('#imp').innerText = `${lcpNeedsImprovement} (${impPercent})%`;
    document.querySelector('#poor').innerText = `${lcpPoor} (${poorPercent})%`;
    document.querySelector('#avg').innerText = `${lcpAvg}`;

    zingchart.render({
      id: "lcp_pie",
      data: {
        type: 'pie',
        title: {
          text: "Largest Contentful Paint (LCP)",
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
            values: [lcpGood],
            text: 'good',
            'background-color': "green"
          },
          {
            values: [lcpNeedsImprovement],
            text: 'needs improvement',
            'background-color': "orange"
          },
          {
            values: [lcpPoor],
            text: 'poor',
            'background-color': "red"
          }
        ]
      }
    });
  </script>

</body>

</html>

