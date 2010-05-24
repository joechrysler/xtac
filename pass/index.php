<html>
<head>
<title>
   SVSU Administrator Tools
</title>

<!-- Style Sheets -->
<link rel="stylesheet" href="css/master.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" type="text/css" href="../jquery.autocomplete.css" />

<!-- Scripts -->
<script type="text/javascript" src="js/lib/jquery.js"></script>
<script type='text/javascript' src='js/lib/jquery.bgiframe.min.js'></script>
<script type='text/javascript' src='js/lib/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='js/lib/jquery.autocomplete.min.js'></script>
<script type="text/javascript">
$().ready(function() {

   function log(event, data, formatted)
      {
      $("<li>").html( !data ? "No match!" : "Selected: " + formatted).appendTo("#result");
      }

   function formatItem(row)
      {
      return row[0] + " (<strong>id: " + row[1] + "</strong>)";
      }

   function formatResult(row)
      {
      return row[0].replace(/(<.+?>)/gi, '');
      }
   $("#singleBirdRemote").autocomplete("search.php", {
      width: 260,
      selectFirst: false,
      formatItem: function(data) {
      if (data)
      return data[0] + " : " + data[1];
      },
      });
   $("#searchField").autocomplete("search.php", {
      width: 260,
      selectFirst: false,
      formatItem: function(data) {
         if (data)
         {
            alert("Yay");
            return data[0] + " : " + data[1];
            }
         },
      });

   $("#searchField").result(function(event, data, formatted) {
      if (data)
         $(this).parent().next().find("input").val(data[1]);
      });

   });
</script>
</head>

<body>
   <h1>Admin Tools</h1>
   <form>
   <p>
      <input type="text" id="searchField" />
      </p>
      <input type="button" value="Go &#x2192;" />
   </form>
   <?php include 'templates/xtac-presentation.html';?>
</body>
</html>
