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
  <title>CLS Report</title>

  <link rel="stylesheet" href="reports.css">

  <script src="https://cdn.zingchart.com/zingchart.min.js"></script>
  <script src="https://cdn.zinggrid.com/zinggrid.min.js" defer></script>
</head>

<body>
  <nav style="text-align:right; font-size:20px">
    <a href="../index.php">Home</a>
    <a href="../logout.php">Logout</a>
  </nav>
  <h1>CLS Report</h1>

  <script>
    <?php 
      // connect
      $mysqli = new mysqli("localhost", "root", "Carbon742!@#", "hw3");
      if (mysqli_connect_errno()) {
          printf("Connect failed: %s\n", mysqli_connect_error());
          exit();
      }
      // initialize arrays
      $clsGrid = [];
      $clsCount = 0;
      if ($result = $mysqli->query("SELECT * FROM cls")) {
        $clsGrid = $result->fetch_all(MYSQLI_ASSOC);
        $clsCount = $result->num_rows;
        $result->close();
      }
      $cls = mysqli_query($mysqli, "SELECT * FROM cls");
    ?>
    // for zinggrid
    var clsGridValues = <?php echo json_encode($clsGrid) ?>;

    // for zingchart
    var clsData = [<?php
      while($info=mysqli_fetch_array($cls))
      // select vitals column
      echo '\''.$info['vitalsScore'].'\''.',';
      ?>];
    var clsGood = 0;
    var clsNeedsImprovement = 0;
    var clsPoor = 0;
    for (let i = 0; i < clsData.length; i++) {
      if (clsData[i] == 'good') {
        clsGood++;
      }
      else if (clsData[i] == 'poor') {
        clsPoor++;
      }
      else {
        clsNeedsImprovement++;
      }
    }

    // for dynamic report
    var goodPercent = clsGood/(clsGood+clsNeedsImprovement+clsPoor) * 100;
    var impPercent = clsNeedsImprovement/(clsGood+clsNeedsImprovement+clsPoor) * 100;
    var poorPercent = clsPoor/(clsGood+clsNeedsImprovement+clsPoor) * 100;
    goodPercent = goodPercent.toFixed(2);
    impPercent = impPercent.toFixed(2);
    poorPercent = poorPercent.toFixed(2);

    // to calculate averages, query from db again and select data column
    <?php 
        $cls = mysqli_query($mysqli, "SELECT * FROM cls");
    ?>
    var clsVals = [<?php
        while($info=mysqli_fetch_array($cls))
        echo $info['data'].',';
        ?>];
    var clsAvg = 0;
    for (let i = 0; i < clsVals.length; i++) {
        clsAvg += clsVals[i];
    }
    clsAvg = clsAvg/clsVals.length;
    clsAvg = clsAvg.toFixed(2);

    <?php
      $mysqli->close();
    ?>
  </script>
  
    <zing-grid id="clsGrid"
        editor contextmenu
        caption="CLS Data From Visitors"
        sort
        search
        pager
        page-size="5"
        layout="row"
        viewport-stop
        >
    </zing-grid>

  <figure id="cls_pie"></figure>

  <h2>How is CLS performance for users?</h2>
  <article>
    <p>
        Cumulative Layout Shift (CLS) measures visual stability and is one of 
        three important web vitals that apply to all web pages. It reports the 
        sum total of all individual layout shift scores for every unexpected 
        layout shift that occurs during the entire lifespan of the page. 
        To provide good user experience, sites should have a CLS score of less
        than 0.1. Improvement is needed if CLS is between 0.1 to 0.25 seconds, 
        and is considered poor if is over 0.25.
    </p>
    <p>
        Out of the <?php echo $clsCount ?> visits to the main carbonchen.site page,
        <ul>
            <li>
                <span id="good"></span> experience good CLS,
            </li>
            <li>
                <span id="imp"></span> experience "needs improvement" CLS,
            </li>
            <li>
                <span id="poor"></span> experience poor CLS.
            </li>
        </ul>
    </p>
    <p>
        The average CLS score among all users is <span id="avg"></span>.
    </p>
  </article>

  <script>
    document.querySelector('#clsGrid').data = clsGridValues;
    document.querySelector('#good').innerText = `${clsGood} (${goodPercent})%`;
    document.querySelector('#imp').innerText = `${clsNeedsImprovement} (${impPercent})%`;
    document.querySelector('#poor').innerText = `${clsPoor} (${poorPercent})%`;
    document.querySelector('#avg').innerText = `${clsAvg}`;

    zingchart.render({
      id: "cls_pie",
      data: {
        type: 'pie',
        title: {
          text: "Cumulative Layout Shift (CLS)",
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
            values: [clsGood],
            text: 'good',
            'background-color': "green"
          },
          {
            values: [clsNeedsImprovement],
            text: 'needs improvement',
            'background-color': "orange"
          },
          {
            values: [clsPoor],
            text: 'poor',
            'background-color': "red"
          }
        ]
      }
    });
  </script>

</body>

</html>

