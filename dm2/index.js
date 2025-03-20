const sleep = (s) => new Promise((res) => setTimeout(res, s * 1000));
let isWait = true;

async function f1($char, $time = 1) {
  for (let index = 0; index < 5; index++) {
    process.stdout.write($char ?? "#");
    if (isWait) {
      await sleep($time);
    }
  }
}

async function main() {
  f1(".");
  f1("-",0.5);
}
main();
