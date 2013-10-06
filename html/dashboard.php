<div style="max-width:1000px; max-height:600px; padding:20px;overflow:auto; float:left;">
<div>
<table width="80%" cellpadding="0" cellspacing="0">
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
            <td style="background-color:rgb(225,225,225)"><?php echo $row ?></td>
    <?php
        for($col = 1; $col <= 30; $col++)
        {
    ?>
            <td><?php echo $row .  ' - ' . $col?></td>
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

<div style="width:260px; float:right; background-color:#999; padding:8px;">
Result :
<span id="res"></span>
</div>

<script src="jquery-1.9.0.js"></script>
<script>
$(document).ready(function()
{
	function unSelectAll()
	{
		$('table td').each(function() {
		  	$(this).removeClass("highlighted");
			$(this).removeClass("selstart");
		});
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
	
	var isMouseDown = false;
	var dragStartRow, dragStartCol;
	var colTotIndex = $('table > tbody').find("> tr:first > td").length - 1;
	var rowTotIndex = $('table tr:last').index();
	
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
		
		$('#res').text('Row - ' + curRow + ', Col - ' + curCol);
		return false; 
	  })	// Mouse DragOver
	  .mouseover(function () {
		if (isMouseDown) {
			var curCol = $(this).index();
			var curRow = $(this).closest('tr').index();
			selectRange(dragStartRow, dragStartCol, curRow, curCol);
		}
	  })
	  .bind("selectstart", function () {
		return false; // prevent text selection in IE
	  });
	  
	  
	$("table thead th").mousedown(function () {
		var curCol = $(this).index();
		$('#res').text('Col - ' + curCol);
		unSelectAll();
		if( curCol == 0 )
		{
			selectRange(0, 1, rowTotIndex, colTotIndex);
		}
		else
		{
			$('table tr td:nth-child(' + (curCol + 1) + ')').addClass("highlighted");
		}
	});
  
  
	$(document).mouseup(function () {
		isMouseDown = false;
	  });
});
</script>

<style type="text/css">
table th { background-color:rgb(225,225,225); }
table td, th { padding:10px; border: .2px inset rgb(200,200,200);}
table td.highlighted { background-color: rgba(160, 195, 255, .5); z-index:50; }
table td.selstart { border: 1px solid rgb(0,0,255); }
</style>