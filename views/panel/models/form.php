<?php
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Extensions\I18n\I18n;
use Scabbia\Extensions\Views\Views;
use Scabbia\Extensions\Session\Session;

?>
<?php Views::viewFile('{core}views/panel/header.php'); ?>
	<form method="POST" action="<?php echo $postback; ?>">
		<table id="pageMiddleTable">
			<tr>
				<td id="pageMiddleSidebar">
                    <div class="menuDivContainer">
                        <?php Views::viewFile('{core}views/panel/sectionError.php', Panel::$module); ?>

                        <div class="menuDiv">
                            <fieldset>
                                <legend><?php echo I18n::_($module['singularTitle']); ?></legend>
                                <?php
                                foreach ($fields as $tField) {
                                    echo $tField['html'];
                                }
                                ?>
                                <input type="submit" class="btn btn-primary pull-right" value="<?php echo I18n::_('Submit'); ?>" />
                            </fieldset>
                        </div>
                    </div>
					<div class="clearfix"></div>
				</td>
				<td id="pageMiddleSidebarToggle">
					&laquo;
				</td>
				<td id="pageMiddleContent">
					<div class="topLine"></div>
					<div class="middleLine">

					</div>
					<div class="bottomLine"></div>
					<div class="clearfix"></div>
				</td>
				<td id="pageMiddleExtra">
				</td>
			</tr>
		</table>
	</form>
<?php Views::viewFile('{core}views/panel/footer.php'); ?>