function draw_truss(nodes, elements, alpha) {

    //unseerialize
    factor = 10;
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
        draw_point(context, factor * nodes[n].posx, factor * nodes[n].posy, alpha);
    }
    for (var e in elements) {
        draw_line(context, factor * elements[e].posx1, factor * elements[e].posy1, factor * elements[e].posx2, factor * elements[e].posy2, alpha);
    }

    //draw modified
    for (var n in nodes) {
        draw_point(context, factor * nodes[n].mposx, factor * nodes[n].mposy, 1);
    }
    for (var e in elements) {
        draw_line(context, factor * elements[e].mposx1, factor * elements[e].mposy1, factor * elements[e].mposx2, factor * elements[e].mposy2, 1);
    }
}

function draw_point(context, posx, posy, alpha) {
    context.beginPath();
    context.arc(posx, posy, 5, 0, 2 * Math.PI, false);
    context.globalAlpha = alpha;
    context.fill();
}

function draw_line(context, posx1, posy1, posx2, posy2, alpha) {
    context.beginPath();
    context.moveTo(posx1, posy1);
    context.lineTo(posx2, posy2);
    context.globalAlpha = alpha;
    context.lineWidth = 3;
    context.stroke();
}
