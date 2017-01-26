function draw_truss(nodes, elements, legend, alpha) {

    //unseerialize
    nodes = JSON.parse(nodes);
    elements = JSON.parse(elements);
    legend = JSON.parse(legend);

    //get canvas element
    var canvas = document.getElementById('myCanvas');
    var context = canvas.getContext('2d');
    context.save();
    context.translate(100, 800);
    context.scale(1, -1);

    //draw actual
    for (var n in nodes) {
        draw_point(context, nodes[n].posx, nodes[n].posy, alpha);
    }
    for (var e in elements) {
        draw_line(context, elements[e].posx1, elements[e].posy1, elements[e].posx2, elements[e].posy2, alpha, false, '');
    }

    //draw modified
    for (var n in nodes) {
        draw_point(context, nodes[n].mposx, nodes[n].mposy, 1);
    }
    for (var e in elements) {
        draw_line(context, elements[e].mposx1, elements[e].mposy1, elements[e].mposx2, elements[e].mposy2, 1, true, elements[e]);
    }

    //save canvas
    context.save();

    //draw actual
    for (var n in nodes) {
        write_name(context, n, nodes[n], alpha)
    }

    //save canvas
    context.save();

    //draw legend
    context.scale(1, -1);
    for (var l in legend) {

        context.fillStyle = (legend[l].color);
        context.fillRect((1000),(-400 - l * 20),50,20);
        context.font = "12px Arial";
        context.fillStyle = 'black';
        context.fillText(legend[l].startval + ' lbs to ' + legend[l].endval+ ' lbs' ,1060 ,(-385 - l * 20));
    }
}

function write_name(context, n,node, alpha) {
    context.font = "12px Arial";
    context.fillText(n,node.posx+12,node.posy+12);
}

function draw_point(context, posx, posy, alpha) {
    context.beginPath();
    context.arc(posx, posy, 5, 0, 2 * Math.PI, false);
    context.globalAlpha = alpha;
    context.fill();
}

function draw_line(context, posx1, posy1, posx2, posy2, alpha, type, ele) {
    context.beginPath();
    context.moveTo(posx1, posy1);
    context.lineTo(posx2, posy2);
    if(type){
        context.strokeStyle = ele.color;
    }
    context.globalAlpha = alpha;
    context.lineWidth = 3;
    context.stroke();
}
