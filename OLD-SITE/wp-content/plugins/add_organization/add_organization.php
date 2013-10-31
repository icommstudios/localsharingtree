<?php
/*
Plugin Name: Add Organizations
Plugin URI: rvtechnologies.co.in
Description: Allows to add Organizations.
Author: Royal Tyagi
Version: 1.0
*/
wp_enqueue_script('validate_my', plugins_url('/js/validate_my.js',__FILE__) );
add_action('admin_menu', 'randomquote_menu');

function randomquote_menu()
{
    $allowed_group = 'manage_options';

    //Add the admin panel for randomquote
    if(function_exists('add_menu_page'))
    {
        add_menu_page(__('Add Organization','rndmqmker'), __('Add Organization','rndmqmker'),$allowed_group,'rndmqmker','edit_options');
    }
}

function edit_options()
{
    global $wpdb;

    //get everything if it is available...
    $action     = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'add';
    //for deleting
    $id   = !empty($_REQUEST['quote_id']) ? $_REQUEST['quote_id'] : '';
    //the quote itself
    $org_name      = !empty($_REQUEST['org_name']) ? $_REQUEST['org_name'] : '';
    //the author of the quote... Anonymous if left blank...
    $author     = !empty($_REQUEST['author']) ? $_REQUEST['author'] : 'Anonymous';

    //check to see if the database is installed... and if they have the right version later...
    check_random_quote_database();

    //if there is a quote ready to be saved let's save it right away
    
    if (!empty($_POST['id'])) {
        
        $org_name = str_replace("'",'', $_POST["org_name"]);
        global $wpdb;
        $query = "UPDATE all_organizations SET org_name='" . $org_name . "', contact_name='" . $_POST["contact_name"] . "', email='" . $_POST['email'] . "' WHERE id='" . $_POST['id'] . "'";
        
        $result = $wpdb->query($query);
        if ($result) { ?>
            <div class="updated"><p><?php _e('Organization has been Updated successfully','rndmqmker'); ?></p></div>
       <?php
        }
        
    } else {

            if(!empty($org_name))
            {
                //some security.
                $org_name = trim(htmlspecialchars(mysql_real_escape_string($org_name)));                
                
                //trust no one and escape everything.
                $contact_name = trim(htmlspecialchars(mysql_real_escape_string($_REQUEST['contact_name'])));
                $email  = trim(htmlspecialchars(mysql_real_escape_string($_REQUEST['email'])));

                // Check if organization already exist or not

                $sql = "SELECT * FROM " . all_organizations . " WHERE org_name='" . mysql_real_escape_string($org_name) . "'";

                $exist_org = $wpdb->get_results($sql);

                if (!empty($exist_org[0]->org_name)) { ?>
                    <div class="error"><p><strong><?php _e('Failure','rndmqmker'); ?></strong><?php _e('This Organization already exist');?></p></div>
                <? } else {
                    
                    $org_name = str_replace("'",'', $org_name);
                    //the query.
                    $sql = "INSERT INTO " . all_organizations . " (org_name, contact_name, email) VALUES ('" . $org_name . "','" . $contact_name . "','" . $email . "');";
                    //run the query.
                    $wpdb->get_results($sql);
                    //See if the query successfully went through...
                    $sql = "SELECT * FROM " . all_organizations . " WHERE org_name='" . mysql_real_escape_string($org_name) . "'";
                        $results = $wpdb->get_results($sql);
                        //if it returned a non empty quote we know it worked
                        if(!empty($results[0]->org_name))
                        {
                            //pre setup class for the div to display that nice updated message...
                            ?>
                            <div class="updated"><p><?php _e('Organization saved successfully','rndmqmker'); ?></p></div>
                            <?php
                        }
                        else
                        {
                            //pre setup error class for the div to display that nice error message...
                            ?>
                            <div class="error"><p><strong><?php _e('Failure','rndmqmker'); ?></strong><?php _e(' It did not save, sorry, try again');?></p></div>
                            <?php
                        }

                }

            }

    }

    //will be 'add' by default
    if($action == 'add')
    {
        //wrap will make everything look nice and wordpress like.
        ?>
        <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div>
        <h2><?php _e('Add Oganization','rndmqmker'); ?></h2>
        <?php
        //put the add quote form in
        rq_add_form();
        //show a table of all the quotes.
        rq_display_quotes();
        ?>
        </div>
        <?php
    }
    
    if($action == 'edit')
    {
        //wrap will make everything look nice and wordpress like.
        ?>
        <div class="wrap">
        <h2><?php _e('Edit Organization','rndmqmker'); ?></h2>
        <?php
        //put the add quote form in
        rq_add_form($_REQUEST['quote_id']);
        
        //show a table of all the quotes.
        //rq_display_quotes();
        ?>
        </div>
        <?php
    }
    
    //if they are deleting a quote
    else if($action == 'delete')
    {
        //do we have a quote id?
        if(!empty($id))
        {
            //the query
            $sql = "DELETE FROM " . all_organizations . " WHERE id='" . mysql_real_escape_string($id) . "'";
            //run the query
            $wpdb->get_results($sql);
            //check that it worked... The query
            $sql = "SELECT * FROM " . all_organizations . " WHERE id='" . mysql_real_escape_string($id) . "'";
            //run it
            $results = $wpdb->get_results($sql);
            //did we get anything back? If so it didn't delete
            if(empty($results) || empty($results[0]->id))
            {
                ?>
                <div class="updated"><p><?php _e('Deleted successfully','rndmqmker'); ?></p><p><a href ="<?php echo $_SERVER['PHP_SELF'] ?>?page=rndmqmker"><?php _e('Click here to go Back on list','rndmqmker'); ?></a></p></div>
            <?php
            }
            else
            {
                ?>
                <div class="error"><p><strong><?php _e('Failure','rndmqmker'); ?></strong><?php _e('It did not delete, sorry, try again');?></p></div>
            <?php
            }
        }
        else
        {
            ?>
            <div class="error"><p><strong><?php _e('Hey...','rndmqmker'); ?></strong><?php _e('You can\'t delete a quote without a quote id' );?></p></div>
            <?php
        }
    }
}

// [randomquote]
function random_quote($atts)
{
    return $quotes[rand(0,(count($quotes)-1))];
}

add_shortcode('randomquote', 'random_quote');
function check_random_quote_database()
{
    global $wpdb;
	$rq_db_version = 1.0;
	$table_name = all_organizations;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
	{
		$sql = "CREATE TABLE " . $table_name . " (
		id INT(11) NOT NULL AUTO_INCREMENT,
		org_name VARCHAR(100) NOT NULL DEFAULT 'NULL',
		contact_name VARCHAR(100) NOT NULL DEFAULT 'NULL',
		email VARCHAR(100) NOT NULL DEFAULT 'NULL',
		PRIMARY KEY  id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		$insert = "INSERT INTO " . $table_name .
		" (org_name, contact_name, email) " .
            "VALUES ('This one is Fake','Royal Tyagi','test@gmail.com')";
 
		$results = $wpdb->query( $insert );

		add_option("rq_db_version", $rq_db_version);
	}
        //for updates
        $installed_ver = get_option( "cl_db_version" );
}

function rq_display_quotes()
{
        //we need the wp database functions
	global $wpdb;

        //get all the quotes and authors
        $quotes = $wpdb->get_results( 'SELECT * FROM ' . all_organizations . ' ORDER BY ' . all_organizations . '.id ASC');

        //if we got quotes back...
	if( !empty($quotes))
	{
		?>
        <table class="widefat page fixed" width="100%" cellpadding="3" cellspacing="3">
			<thead>
			<th><?php _e('ID','rndmqmker') ?></th>
			<th><?php _e('Organization/School','rndmqmker') ?></th>
			<th><?php _e('Contact Person','rndmqmker') ?></th>
			<th><?php _e('Email Address','rndmqmker') ?></th>
			<th><?php _e('Delete/Edit','rndmqmker') ?></th>
			</thead>
			<tbody>
        <?php
                //loop through the results of all the quotes
		$class = '';
		foreach($quotes as $quote)
		{
            //make the rows look nice by alternating the colors of the row.. Prebuilt feature
			$class = ($class == 'alternate') ? '' : 'alternate';
			
			$url = get_bloginfo('wpurl');
            //output the info into the table.. It will call itelf when they press delete... PHP_SELF
			?>
			<tr class="<?php echo $class; ?>">
				<th scope="row"><?php echo $quote->id;?></th>
				<td><?php echo $quote->org_name; ?></td>
                <td><?php echo $quote->contact_name; ?></td>
                <td><?php echo $quote->email; ?></td>
				<td><a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=rndmqmker&amp;action=delete&amp;quote_id=<?php echo $quote->id;?>" class="delete" onclick="return confirm('<?php _e('Are you sure you want to delete this quote?','rndmqmker'); ?>')"><?php echo __('Delete','rndmqmker'); ?></a>&nbsp;&nbsp;&nbsp;<a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=rndmqmker&amp;action=edit&amp;quote_id=<?php echo $quote->id;?>" class="edit" onclick="return confirm('<?php _e('Are you sure you want to edit this organization?','rndmqmker'); ?>')"><?php echo __('Edit','rndmqmker'); ?></td>
			</tr>
        <?php
		}
		?>
		</tbody>
		</table>
		<?php
	}
	else
	{
            //otherwise there is nothing to show
		?>
		<p><?php _e("There are no organization in the database yet!", 'rndmqmker');?></p>
		<?php
	}
}

function rq_add_form($id = null)
{
    // here is the form they fillout. to make a new quote...
    
        if (!empty($id)) {

            global $wpdb;
            $sql = "SELECT * FROM " . all_organizations . " WHERE id ='" . $id . "'";
            $results = $wpdb->get_results($sql);
        }
        ?>
    <div class="wrap">
	<form class="wrap" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=rndmqmker" onsubmit="return CheckFormData();">
            <div id="linkadvanceddiv" class="postbox">
                <div style="float: left; width: 100%; clear: both;" class="inside">
                <table cellpadding="5" cellspacing="5">
                <tr>
                    <td><?php _e('Organization/School', 'rndmqmker'); ?></td>
                    <td>
                        <input type="text" name="org_name" id="org_name" class="input" size="40" maxlength="200" value="<?php echo $results[0]->org_name; ?>"/>                    
                    </td>
                </tr>
                <tr>
                    <td><?php _e('Contact Name','rndmqmker');?></td>
                    <td>
                        <input type="text" name="contact_name" id="contact_name" class="input" size="40" maxlength="200" value="<?php echo $results[0]->contact_name; ?>"/>
                    </td>
                </tr>
                <tr>
                    <td><?php _e('Email','rndmqmker');?></td>
                    <td>
                        <input type="text" name="email" id="email" class="input" size="40" maxlength="200" value="<?php echo $results[0]->email; ?>"/>
                    </td>
                </tr>
                <tr><td>
                   <input type="hidden" name="id" value="<?php echo $results[0]->id; ?>" />
                   <input type="submit" name="save" class="button bold" value="<?php _e('Save', 'rndmqmker'); ?>&raquo;" />
                </td></tr>
                </table>
                </div>
            </div>
        </form>
    </div>
        <?php
}
?>
