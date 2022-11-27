/*
 * I was inititally planning to just copy a couple of levels from somewhere, but doing that manually turned out to be a pain
 * so i've googled for a level generator, first one i found was this
 * 
 * https://github.com/AlliBalliBaba/Sokoban-Level-Generator
 * 
 * looked into how it stores level data and made a messy converter script
 * 
 * usage: run AlliBalliBaba's js level builder (i just went to this page https://alliballibaba.github.io/Sokoban-Level-Generator/)
 * 
 * generate level, click on optimize button , run this js code in browser console.
 * you'll get 2 strings: coordinatesMultipleByEntityId victory condition "array"
 * and the level's entityIdArray itself. Levels might not be perfect, but it beats manual labor. Thanks AlliBalliBaba!
 * 
 *   
 */
var arStr = '';

var xyArr = [];
var butAr = [];

var butStr = "['501'=>[";

currentLvl.buttons.forEach((btn,i) => {
    if (undefined == butAr[btn.y]){
        butAr[btn.y] = [];
    }
    if (undefined == butAr[btn.y][btn.x]){
        butAr[btn.y][btn.x] = true;
    }
    butStr = butStr + "[" + btn.x + "," + btn.y + "],"; 
});

butStr = butStr + "]]"

console.log(butStr);

currentLvl.nodes.forEach((xrow,i) => {
    
    xrow.forEach((tile,j) => {

        var tileStr = '123';
        if (tile.hasBox === true){
            tileStr = '[123,501]';
        }
        if (tile.x === currentLvl.playerstartX && tile.y === currentLvl.playerstartY){
            tileStr = '[123,999]';
        }
        if (butAr[tile.y]!== undefined && butAr[tile.y][tile.x] === true){
            tileStr = '125';
        }
        if (tile.wall === true){
            tileStr = '124';
        }



        if (undefined == xyArr[tile.y]){
            xyArr[tile.y] = [];
        }
        if (undefined == xyArr[tile.y][tile.x]){
            xyArr[tile.y][tile.x] = tileStr;
        }

        
        
    });
    
});

xyArr.forEach((xrow,i) => {
    arStr = arStr + '[' + xrow.join(',') + '],\r\n';
})

console.log(arStr);