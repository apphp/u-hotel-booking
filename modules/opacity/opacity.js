function opacity_onmouseover(el){
    el.style.opacity=1;
    if(typeof el.filters != 'undefined') el.filters.alpha.opacity=100;
}

function opacity_onmouseout(el){
    el.style.opacity=0.74;
    if(typeof el.filters != 'undefined') el.filters.alpha.opacity=74;
}