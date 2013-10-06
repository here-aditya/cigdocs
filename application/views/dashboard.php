<div class="navbar navbar-inverse">
    <div class="navbar-inner">
        <div class="container">
            <div class="span10">
                <h2>Gdocs Spreadsheet - Chart</h2>
            </div>
            <div class="span2 pull-right block_rtop ">
                <a href="<?php echo site_url('gdoclist/logout')?>">Logout</a>
            </div>   
        </div>
    </div>
</div>


<div class="container-fluid"><!-- container start -->

    <div class="main_container"><!-- main_container start -->
    
        <div class="row-fluid"><!-- header start -->
        
            <div class="span2">
                <!--side navbar -->
                <div class="sidebar-nav">
                    <div class="accordion" id="accordion1">
                    <?php
					if( isset($ss_ws_arr) && is_array($ss_ws_arr))
					{
						$colps_id = 1;
						foreach($ss_ws_arr as $main_menu) 
						{
                    ?>	
                    <div class="accordion-group">
                        <div class="accordion-heading gradient_extrawhite">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#collapse<?php echo $colps_id?>">
                               <?php echo $main_menu['title']?>
                            </a>
                        </div>
                        
                        <div id="collapse<?php echo $colps_id?>" class="accordion-body collapse">
                            <?php
                            foreach($main_menu['wslist'] as $menu) 
							{
                            ?>
                            <div class="accordion-inner gradient_grey">
                                <a href="#<?php echo $main_menu['ssid'] . '_' . $menu['wsid'] ?>"><?php echo $menu['title'] ?></a>
                            </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                        $colps_id++;
                    	}	// outer foreach
					}	// outer if
					else
					{
						echo 'No Data';
					}
                    ?>
                	</div>
                </div><!--/side navbar -->
            </div>
            
            <div class="span7 well gradient_milkywhite" id="graphdata" >
            	<div class="row-fluid">
                    <div class="span5">
                        <h3></h3>
                    </div>
                    <div class="span3">
                        <img src="<?php echo $RPath?>pics/loading_big.gif" id="img_busy" style="display:none"/>
                    </div>
                </div>
                
                <div class="row-fluid">
                    <div style="overflow:scroll; padding:25px; max-height:450px" class="span12 gradient_grey image_padd">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <?php 
                                        for($col = 0; $col <= 30; $col++) 
                                            $col == 0 ? print "<th></th>" : print "<th>$col</th>";
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            for($row = 1; $row <= 100; $row ++)
                            {
                            ?>
                                <tr>
                                    <td  style="background-color:rgb(225,225,225)"><?php echo $row ?></td>
                            <?php
                                for($col = 1; $col<= 30; $col++)
                                {
                            ?>
                                    <td></td>
                            <?php
                                }
                            ?>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="span3 well gradient_milkywhite">
           		<h3>Chart :</h3>
                <div id="chartContainer">FusionCharts XT will load here!</div> 
                <label>Select Chart Type :</label>
                <select id="charttype">
                	<option value="<?=$RPath?>Charts/Spline.swf" selected="selected">Spline</option>
                	<option value="<?=$RPath?>Charts/SplineArea.swf">SplineArea</option> 
                    <option value="<?=$RPath?>Charts/Kagi.swf">Kagi</option>
                    <option value="<?=$RPath?>Charts/Waterfall2D.swf">Waterfall2D</option>
                </select>
                <button class="btn btn-primary" id="plotChart">Plot Chart</button>
            </div>
            
        </div><!-- header end -->
         
         
		<div class="row-fluid paddrow">
            <div class="footer">
                <div class="container-fluid">
                    <div class="offset3 reserve">
                        <p class="credit muted">
                            &copy; Gdocs. All rights reserved.
                            <small><a href="#">Disclaimer</a> | <a href="#">Copyright</a> | <a href="#">Site Map</a></small>
                        </p>
                        <div id="res"></div>
                    </div>
                </div>
            </div>
    	</div>
        
	</div><!-- main_container End -->
    
</div><!-- container End -->



<!------------------------------------ jQuery Part of Program --------------------------- -->
<script>
$(document).ready(function()
{
	function clearTable()
	{
		$('table td').each(function() {
			if( $(this).index() != 0 )
				$(this).html('');
		});
	}
	
	function fetchTableData( ssid, wsid )
	{
		var request = $.ajax({
								url: "<?php echo site_url('gdoclist/showTable')?>",
								data: { ssid: ssid, wsid: wsid },
								type: "POST",
								dataType: "json"
							});
		clearTable();
		request.done(function(msg) {
			if(msg)
			{
				$.each(msg, function(k, v) {
					$('table tbody tr').eq(v.row-1).find('td').eq(v.col).html(v.val);
				});
				$('#img_busy').hide();
			}
		});
		request.fail(function(jqXHR, textStatus) {
			alert('Error Occured: '+textStatus);
			$('#img_busy').hide();
		});
	}
	
	$('.accordion-inner a').click(function(){
		$('#img_busy').show();
		$('#graphdata h3').html($(this).text());
		var href = $(this).attr('href').replace('#', '').split('_');
		fetchTableData( href[0], href[1] );
	});
	
	
	// global variables
	var isMouseDown = false;
	var dragStartRow, dragStartCol;
	var colTotIndex = $('table > tbody').find("> tr:first > td").length - 1;
	var rowTotIndex = $('table tr:last').index();
	var selRowStart, selRowEnd, selColStart, selColEnd;
	
	
	function unSelectAll()
	{
		$('table td').each(function() {
		  	$(this).removeClass("highlighted");
			$(this).removeClass("selstart");
		});
		selRowStart = selRowEnd = selColStart = selColEnd = 0;
	}
	
	
	function selectRange(r1, c1, r2, c2)
	{	
		var temp;
		// swap values if r1 > r2
		if(r1 > r2)
		{
			temp = r1;
			r1 = r2;
			r2 = temp;
		}
		
		if(c1 > c2)
		{
			temp = c1;
			c1 = c2;
			c2 = temp;
		}
		
		// initialize globals
		selRowStart = dragStartRow; selRowEnd = r2; selColStart = c1; selColEnd = c2;
		
		// select range of cells	
		temp = c1;	
		while(r1 <= r2)
		{
			c1 = temp;
			while(c1 <= c2)
			{
				$('table tbody tr').eq(r1).find('td').eq(c1).addClass("highlighted");
				c1+=1;
			}
			r1+=1;
		}
	}
	
	function selectColumnRange(c1, c2)
	{
		if(c1 > c2)
		{
			temp = c1;
			c1 = c2;
			c2 = temp;
		}
		
		// initialize globals
		selRowStart = 1; selRowEnd = rowTotIndex; selColStart = c1; selColEnd = c2;
		
		while(c1 <= c2)
		{
			$('table tr td:nth-child(' + (c1 + 1) + ')').addClass("highlighted");
			c1+=1;
		}
	}
	
	
	// Mouse Down
	$("table tbody td").mousedown(function () {
		isMouseDown = true;
		var curobj = $(this);
		var curCol = $(this).index();
		var curRow = $(this).closest('tr').index();
		
		dragStartRow = curRow;
		dragStartCol = curCol == 0 ? 1 : curCol;
		
		// reset all selected td
		unSelectAll();
		
		// select a Row
		if (curCol == 0) {
			selectRange(dragStartRow, dragStartCol, dragStartRow, colTotIndex);
		} else {	// select a Cell
			curobj.addClass("selstart highlighted");
		}
		
		return false; 
	  })	// Mouse DragOver
	  .mouseover(function () {
		if (isMouseDown) {
			var curCol = $(this).index();
			var curRow = $(this).closest('tr').index();
			
			if(curCol == 0 || dragStartCol == 0){
				selectRange(curRow, 1, curRow, colTotIndex);
			}
			else
			{
				selectRange(dragStartRow, dragStartCol, curRow, curCol);
			}
		}
	  })
	  .bind("selectstart", function () {
		return false; // prevent text selection in IE
	  });
	  
	  
	$("table thead th").mousedown(function () {
		isMouseDown = true;
		var curCol = $(this).index();
		dragStartCol = curCol;
		
		unSelectAll();
		if( curCol == 0 )
		{
			selectRange(0, 1, rowTotIndex, colTotIndex);
		}
		else
		{
			selectColumnRange(curCol, curCol);
		}
		return false;
	})
	.mouseover(function () {
		if (isMouseDown) {
			var curCol = $(this).index();
			selectColumnRange(dragStartCol, curCol);
		}
	 })
	 .bind("selectstart", function () {
		return false; // prevent text selection in IE
	  });
  
  
	$(document).mouseup(function () {
		isMouseDown = false;
	  });
	 
	 
	$('#plotChart').click(function() {
		var jsondata = '{"chart":{"caption" : "Fusion Chart Report", "xAxisName" : "X - Axis", "yAxisName" : "Y - Axis"}, "data" : [';
		var loopData = new Array();	
		var innerData = '';		
					
		for(var r1 = selRowStart; r1 <= selRowEnd; r1++)
		{
			//if(r1 != selRowStart && innerData != '') 
				//jsondata += ', ';
			
			var counter = 0;
			innerData = '';
						
			for(var c1 = selColStart; c1 <= selColEnd; c1++)
			{	
				var cellval = $('table tbody tr').eq(r1).find('td').eq(c1).text();
				
				if( $.trim( cellval ) != '' )
				{
					innerData += (counter % 2) == 0 ? '"label" : "' + cellval + '", ' : '"value" : "' + cellval + '" ';
					counter++;
				}
			}
			
			if( innerData != '')
				jsondata += '{' + innerData + '}, ';
		}
		
		jsondata = jsondata.slice(0,-2);
		jsondata += '] }';
		
		$("#chartContainer").updateFusionCharts({"dataSource": jsondata});
		console.log(jsondata);
	});
	
	$('#charttype').change(function() {
		$("#chartContainer").updateFusionCharts({"swfUrl": $(this).val()});
	});
	
	$("#chartContainer").insertFusionCharts({
        swfUrl: "<?=$RPath?>Charts/Spline.swf", 
        width: "270", 
        height: "300", 
        id: "myChartId",
        dataFormat: "json", 
		dataSource: ''
	});
});
</script>

<style type="text/css">
table { background-color:rgb(255,255,255); }
table th { background-color:rgb(225,225,225); }
table td, th { padding:10px; border: 1px solid rgb(180,180,180);}
table td.highlighted { background-color: rgba(160, 195, 255, .5); z-index:50; }
table td.selstart { border: 1px solid rgb(0,0,255); }
</style>