function Semiselect(node)
{	if(node.className!="tab")	   return;	node.className="tabover";	var spanNode=node.childNodes[0];	spanNode.className="innerover";}function Semideselect(node)
{	if(node.className!="tabover")	   return;	node.className="tab";	var spanNode=node.childNodes[0];	spanNode.className="inner";}function __TabsPostBack(node)
{
	var id    = (node.id != null) ? node.id : '';
	document.getElementById('tabid').value = id;
	document.getElementById('frmTabs').submit();}