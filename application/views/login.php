<div class="container">
    
    <div class="row-fluid">
        <div class="span12">
            <div class="span9 offset3"><!--span1-->
            <div class="form-signin gradient_milkywhite form-horizontal">      
            <form method="post" action="<?php echo site_url() . '/Gdoclist/showLogin' ?>">
                <fieldset>
                    <div class="login_divider">
                        <h2 class="form-signin-heading">
                            <img alt="secure" title="secure" src="<?php echo $RPath?>pics/login.png">Login
                        </h2>
                    </div>
                    <div class="control-group">
                        <label for="txt_email" class="control-label">Email Address</label>
                        <div class="controls">
                            <input type="email" required="required" class="input-xlarge" placeholder="Email address" id="txt_email" name="usrid">
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="txt_pswd" class="control-label">Password</label>
                        <div class="controls">
                            <input type="password" required="required" class="input-xlarge" placeholder="Password" id="txt_pswd" name="usrpswd">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <div class="alert alert-block no_disp">
                                <div id="alert_msg"></div>
                            </div>
                            <button id="btn_login" class="btn btn-primary btn-small" type="submit">Sign in</button>
                        </div>
                    </div>
                </fieldset>
			</form>
            </div>
        </div><!--/span1-->
        </div>
    </div><!--/row-->
        
    <div class="row-fluid">   
        <div class="span12">
            <div class="span9 offset3 form-compinfo"><!--span2-->
                <ul class="responsive-utilities-test">
                    <li>Phone<span class="visible-phone">Phone</span></li>
                    <li>Tablet<span class="visible-tablet">Tablet</span></li>
                    <li>Desktop<span class="visible-desktop">Desktop</span></li>
                </ul>
                <div>
                    <p id="scr_val" class="muted"></p>
                </div>
            </div>
        </div>
    </div><!--/row--> 
        
</div>


<!-- Internal Page JS -->
<script>
$(document).ready(function(){
	$('form input').bind('keypress', function(e){
		e.keyCode == 13 ? $('#btn_login').click() : '';
	});
});
// Validate Login Data
<?php 
if( isset($Err) && $Err ) {
?>
$('.alert').removeClass('alert-success').addClass('alert-error').fadeIn('slow');
$('#alert_msg').html('<?php echo $ErrMsg?>');
<?php
}
?>
</script>