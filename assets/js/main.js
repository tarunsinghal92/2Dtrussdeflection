function draw_truss(nodes, elements, alpha) {

    //unseerialize
    nodes = JSON.parse(nodes);
    elements = JSON.parse(elements);

    //get canvas element
    var canvas = document.getElementById('myCanvas');
    var context = canvas.getContext('2d');
    context.save();
    context.translate(400, 400);
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
        draw_line(context, elements[e].mposx1, elements[e].mposy1, elements[e].mposx2, elements[e].mposy2, 1, true, elements[e].type);
    }
}

function draw_point(context, posx, posy, alpha) {
    context.beginPath();
    context.arc(posx, posy, 5, 0, 2 * Math.PI, false);
    context.globalAlpha = alpha;
    context.fill();
}

function draw_line(context, posx1, posy1, posx2, posy2, alpha, type, type_name) {
    context.beginPath();
    context.moveTo(posx1, posy1);
    context.lineTo(posx2, posy2);
    if(type){
        if(type_name == 'compression')context.strokeStyle = 'red';
        if(type_name == 'tension')context.strokeStyle = 'green';
    }
    context.globalAlpha = alpha;
    context.lineWidth = 3;
    context.stroke();
}
