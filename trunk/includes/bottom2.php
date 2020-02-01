<!-- this is the end of the section for CMS -->
							</td>
						</tr>
					</table>
				</td>
				<td width="4"></td>
			</tr>
			<tr>
				<td colspan="3" align="center">
					<table width="900" border="0" cellspacing="0" cellpadding="0" bgcolor="#000000">
						<tr>
							<td colspan="3" bgcolor="#000000" height="7"></td>
						</tr>
						<tr>
							<td>
								<div style="padding-left:5px;">
									<img src="<?=$BF?>images/copyright-apple.png" />
								</div>
							</td>
							<td width="50%" height="45">
								<div class="Copyright">
									<p class="Copyright">Copyright &copy; <?=date('Y')?>, Apple Inc. Internal Use Only. Version <?=$PROJECT_VERSION?></p>
								</div>
							</td>
							<td align="right" style="padding-right:5px;">
								<div class="Copyright">
									<p class="Copyright"><a href="<?=$BF?>index.php?id=3">Marketing Team</a> - <a href="<?=$BF?>index.php?id=2">Contact Us</a>
									<?=(in_array($_SESSION['idType'],array("1","2","3")) ? " - <a href='".$BF."admin/'>Administration Console</a>" : "")?> - <a href="<?=$BF?>events/">Events Console</a></p>
								</div>
							</td>
						</tr>
					</table>
					<img src="<?=$BF?>images/black_bottom.png" />
				</td>
			</tr>
		</table>
</body>
</html>