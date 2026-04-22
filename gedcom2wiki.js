/*
 * gedcom2wiki - pure JavaScript port of the original PHP converter.
 *
 * Takes a GEDCOM 5.5 family genealogy text and returns Wikipedia
 * {{familytree}} template markup. Runs entirely in the browser.
 *
 * Usage: const {output, warning, individualCount, familyCount} = gedcom2wiki(gedcomText);
 *
 * Original PHP: https://sourceforge.net/projects/ged2wiki/
 * License: MIT
 */
(function (root) {
  'use strict';

  const LINE_WIDTH = 160;

  function createRec() {
    return {
      i: 0, level: 0, glevel: undefined,
      ref: '', tag: '', val: '', text: '',
      boxtext: '', boxlength: 0,
      childs: [], sub: [], up: [], down: [], marital: [],
    };
  }

  function parseRecords(text) {
    const rawLines = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
    const lines = rawLines.filter(l => l.length > 0);
    const recs = [];
    for (let i = 0; i < lines.length; i++) {
      const parts = lines[i].split(' ');
      const rec = createRec();
      rec.i = i;
      rec.level = parseInt(parts[0], 10) || 0;
      const test = (parts[1] || '').trim();
      if (test.length > 0 && test[0] === '@' && test[test.length - 1] === '@') {
        rec.ref = test;
        rec.tag = (parts[2] || '').trim();
        parts.splice(0, 3);
      } else {
        rec.tag = test;
        parts.splice(0, 2);
      }
      rec.val = parts.join(' ');
      recs.push(rec);
    }
    return recs;
  }

  function buildChildren(recs) {
    let naid = 0;
    for (let i = 0; i < recs.length; i++) {
      const arr = [];
      for (let j = i + 1; j < recs.length; j++) {
        if (recs[i].level >= recs[j].level) break;
        if (recs[j].tag === 'NAME') {
          recs[i].text = recs[j].val.replace(/\//g, '');
          naid++;
          const naStr = String(naid);
          let s = 'NA' + ('000' + naStr).slice(-3);
          if (s.length > 20) s = s.substring(0, 20);
          if (s.length % 2 === 0) s = s + ' ';
          s = ' ' + s + ' ';
          recs[i].boxtext = s;
          recs[i].boxlength = s.length * 2;
        }
        if (recs[i].level + 1 === recs[j].level) arr.push(recs[j].i);
      }
      recs[i].childs = arr;
    }
  }

  function linkFamilies(recs) {
    for (let i = 0; i < recs.length; i++) {
      if (recs[i].tag !== 'FAM') continue;
      const arr = recs[i].childs;
      for (let a = 0; a < arr.length; a++) {
        const childTag = recs[arr[a]].tag;
        if (childTag === 'WIFE' || childTag === 'HUSB') {
          for (let x = 0; x < recs.length; x++) {
            if (recs[arr[a]].val.trim() !== recs[x].ref.trim()) continue;
            recs[recs[x].i].sub.push(i);

            const arr2 = recs[i].childs;
            let saved = 0;
            for (let a2 = 0; a2 < arr2.length; a2++) {
              if (recs[arr2[a2]].tag.trim() === 'HUSB') {
                for (let y = 0; y < recs.length; y++) {
                  if (recs[arr2[a2]].val.trim() === recs[y].ref.trim()) {
                    saved = recs[y].i;
                    break;
                  }
                }
              }
              if (saved !== 0) break;
            }
            for (let a2 = 0; a2 < arr2.length; a2++) {
              if (recs[arr2[a2]].tag.trim() === 'WIFE') {
                let done = false;
                for (let z = 0; z < recs.length; z++) {
                  if (recs[arr2[a2]].val.trim() === recs[z].ref.trim()) {
                    if (recs[recs[z].i].marital.indexOf(saved) === -1) {
                      recs[recs[z].i].marital.push(saved);
                      done = true;
                      break;
                    }
                  }
                }
                if (done) break;
              }
            }
          }
        }
        if (childTag === 'CHIL') {
          for (let x = 0; x < recs.length; x++) {
            if (recs[arr[a]].val.trim() === recs[x].ref.trim()) {
              recs[recs[x].i].up.push(i);
            }
          }
        }
      }
    }
  }

  function buildDown(recs) {
    for (let i = 0; i < recs.length; i++) {
      if (recs[i].tag.trim() !== 'INDI') continue;
      const arr = recs[i].sub;
      for (let a = 0; a < arr.length; a++) {
        const arr2 = recs[arr[a]].childs;
        for (let a2 = 0; a2 < arr2.length; a2++) {
          if (recs[arr2[a2]].tag.trim() !== 'CHIL') continue;
          for (let x = 0; x < recs.length; x++) {
            if (recs[arr2[a2]].val.trim() === recs[x].ref.trim()) {
              recs[i].down.push(x);
            }
          }
        }
      }
    }
  }

  function assignLevels(recs) {
    for (let i = 0; i < recs.length; i++) {
      if (recs[i].tag.trim() !== 'INDI') continue;
      if (recs[i].up.length === 0) recs[i].glevel = 0;
    }
    function getLevels(i) {
      if (recs[i].glevel === undefined) return;
      const arr = recs[i].down;
      for (let a = 0; a < arr.length; a++) {
        recs[arr[a]].glevel = recs[i].glevel + 1;
        getLevels(arr[a]);
      }
      if (recs[i].marital.length > 0) {
        for (let a = 0; a < arr.length; a++) {
          recs[arr[a]].glevel = recs[recs[i].marital[0]].glevel + 1;
          getLevels(arr[a]);
        }
      }
    }
    for (let i = 0; i < recs.length; i++) {
      if (recs[i].tag.trim() === 'INDI') getLevels(i);
    }
    // Sync marital partners to the max level
    for (let i = 0; i < recs.length; i++) {
      if (recs[i].tag.trim() !== 'INDI') continue;
      for (let j = 0; j < recs.length; j++) {
        if (i === j || recs[j].tag.trim() !== 'INDI') continue;
        if (recs[i].sub.length > 0 && recs[j].marital.indexOf(recs[i].i) !== -1) {
          if (recs[i].glevel > recs[j].glevel) recs[j].glevel = recs[i].glevel;
        }
      }
    }
  }

  function buildLevels(recs) {
    const levels = {};
    const members = [];
    for (let i = 0; i < recs.length; i++) {
      if (recs[i].tag.trim() === 'INDI' && recs[i].glevel !== undefined) {
        if (!levels[recs[i].glevel]) levels[recs[i].glevel] = [];
        levels[recs[i].glevel].push(recs[i].i);
        members.push(recs[i].i);
      }
    }
    const sortedKeys = Object.keys(levels).map(Number).sort((a, b) => a - b);
    // Compact into dense array
    const dense = sortedKeys.map(k => levels[k]);
    // Re-group by common "up" family (PHP loop)
    for (let i = 0; i < dense.length; i++) {
      const level = dense[i];
      if (level.length === 0) continue;
      let arr = recs[level[0]].up;
      const alevel = [level[0]];
      for (let j = 1; j < level.length; j++) {
        if (JSON.stringify(arr) !== JSON.stringify(recs[level[j]].up)) {
          arr = recs[level[j]].up;
        }
        alevel.push(level[j]);
      }
      dense[i] = alevel;
    }
    return { levels: dense, members };
  }

  function interleaveMarital(recs, levels) {
    let maxLevel = 0;
    for (let i = 0; i < levels.length; i++) {
      const level = levels[i];
      const alevel = [];
      for (let j = 0; j < level.length; j++) {
        if (alevel.indexOf(recs[level[j]].i) === -1) alevel.push(recs[level[j]].i);
      }
      const blevel = [];
      for (let a = 0; a < alevel.length; a++) {
        blevel.push(alevel[a]);
        for (let jj = 0; jj < levels.length; jj++) {
          for (let j = 0; j < levels[jj].length; j++) {
            const target = levels[jj][j];
            if (recs[target].marital.length > 0 &&
                recs[target].marital.indexOf(recs[alevel[a]].i) !== -1) {
              blevel.push(target);
            }
          }
        }
      }
      levels[i] = blevel;
      if (blevel.length > maxLevel) maxLevel = blevel.length;
    }
    // Reorder each level by family groups
    for (let i = 0; i < levels.length; i++) {
      const newlevel = {};
      for (let j = 0; j < levels[i].length; j++) {
        const indiRef = recs[levels[i][j]].ref;
        for (let k = 0; k < recs.length; k++) {
          if (recs[k].tag !== 'FAM') continue;
          const arr = recs[k].childs;
          for (let a = 0; a < arr.length; a++) {
            if (recs[arr[a]].val.trim() === indiRef.trim()) {
              const famRef = recs[k].ref.trim();
              if (!newlevel[famRef]) newlevel[famRef] = [];
              newlevel[famRef].push(levels[i][j]);
            }
          }
        }
      }
      const famKeys = Object.keys(newlevel).sort();
      const adjlevel = [];
      for (const kl of famKeys) {
        for (const vs of newlevel[kl]) {
          if (adjlevel.indexOf(vs) !== -1) continue;
          adjlevel.push(vs);
          for (const kt of famKeys) {
            for (const vi of newlevel[kt]) {
              if (recs[vi].marital.indexOf(vs) !== -1) {
                if (adjlevel.indexOf(vi) === -1) adjlevel.push(vi);
              }
            }
          }
        }
      }
      const adjlevel2 = [];
      for (let m = 0; m < adjlevel.length; m++) {
        if (recs[adjlevel[m]].marital.length === 0) {
          adjlevel2.push(adjlevel[m]);
          for (let n = 0; n < adjlevel.length; n++) {
            if (m === n) continue;
            if (recs[adjlevel[n]].marital.indexOf(adjlevel[m]) !== -1) {
              if (adjlevel2.indexOf(adjlevel[n]) === -1) adjlevel2.push(adjlevel[n]);
            }
          }
        } else {
          let flag = false;
          for (let p = 0; p < adjlevel.length; p++) {
            if (recs[adjlevel[m]].marital.indexOf(adjlevel[p]) !== -1) { flag = true; break; }
          }
          if (!flag) adjlevel2.push(adjlevel[m]);
        }
      }
      levels[i] = adjlevel2;
    }
    return maxLevel;
  }

  function defaultLine() {
    return new Array(LINE_WIDTH).fill('');
  }

  const VALID_TILES = new Set(['!','-',':','L','J',',','D','.','^','v','+','`','\'','y','~']);
  function sanitizeTile(v) {
    if (VALID_TILES.has(v)) return v;
    if (v && (v[0] === '[' || v[0] === '{')) return v;
    if (v && v[0] === 'x') return v.substring(1);
    return '';
  }

  function makeRelation(Lines, recs, f, line) {
    const oldlines = [];
    const delines = [];
    let flag = true;
    for (let i = line + 1; i < Lines.length && flag; i++) {
      for (let j = 0; j < Lines[i].length; j++) {
        if (Lines[i][j] && Lines[i][j][0] === '{') { flag = false; break; }
      }
      if (flag) { oldlines.push(Lines[i]); delines.push(i); }
    }
    if (oldlines.length > 0) {
      const newlines = Lines.filter((_, idx) => delines.indexOf(idx) === -1);
      Lines.length = 0;
      for (const l of newlines) Lines.push(l);
    }

    const sonlines = [Lines[line].slice(), Lines[line].slice(), Lines[line].slice(), Lines[line].slice()];
    let mx = -1, mn = 100;
    const joints = [];
    for (let i = 0; i < LINE_WIDTH; i++) {
      if (sonlines[0][i] !== '' && recs[sonlines[0][i]] && recs[sonlines[0][i]].up.indexOf(f) !== -1) {
        sonlines[0][i] = '!';
        if (mx < i) mx = i;
        if (mn > i) mn = i;
        joints.push(i);
      }
    }
    if (joints.length > 0) {
      sonlines[1][joints[0]] = ',';
      sonlines[1][joints[joints.length - 1]] = '.';
      for (let i = mn + 1; i < mx; i++) {
        if (sonlines[1][i] !== '!') sonlines[1][i] = '-';
      }
      for (let i = 1; i < joints.length - 1; i++) sonlines[1][joints[i]] = 'v';
      const mid = Math.floor((mn + mx) / 2);
      if (sonlines[1][mid - 1] === '-' && sonlines[1][mid + 1] === '-') {
        sonlines[1][mid] = sonlines[1][mid] === 'v' ? '+' : '^';
      } else {
        sonlines[1][mid] = '!';
      }
      sonlines[2][mid] = '!';
      sonlines[3][mid] = '[' + f + ']';
    }
    for (let i = 0; i < LINE_WIDTH; i++) {
      for (let w = 0; w < sonlines.length; w++) {
        sonlines[w][i] = sanitizeTile(sonlines[w][i]);
      }
    }
    if (oldlines.length) {
      for (let i = 0; i < oldlines.length; i++) {
        if (sonlines[i]) {
          for (let j = 0; j < sonlines[i].length; j++) {
            if (oldlines[i][j] !== '') sonlines[i][j] = oldlines[i][j];
          }
        } else {
          sonlines.push(oldlines[i]);
        }
      }
    }
    for (const sl of sonlines) Lines.push(sl);
    return line;
  }

  function makeMarital(Lines, recs, s, wifes, line) {
    const oldlines = [];
    const delines = [];
    let flag = true;
    for (let i = line - 1; i >= 0 && flag; i--) {
      for (let j = 0; j < Lines[i].length; j++) {
        if (Lines[i][j] && Lines[i][j][0] === '[') { flag = false; break; }
      }
      if (flag) { oldlines.push(Lines[i]); delines.push(i); }
    }
    if (oldlines.length > 0) {
      const newlines = Lines.filter((_, idx) => delines.indexOf(idx) === -1);
      Lines.length = 0;
      for (const l of newlines) Lines.push(l);
      line = line - oldlines.length;
    }

    const wifelines = [Lines[line].slice(), Lines[line].slice()];
    const ends = [];
    const endlines = [];
    let si = 0, w = 0, found = 0;
    for (let i = 0; i < LINE_WIDTH; i++) {
      if (wifelines[w][i] === s) {
        si = i;
        wifelines[w][i] = ':';
        wifelines[w + 1][i] = 'L';
        for (let j = LINE_WIDTH - 1; j > i; j--) {
          if (wifes.indexOf(wifelines[w][j]) !== -1) {
            if (found === 0) {
              wifelines[w][j] = ':';
              wifelines[w + 1][j] = 'J';
              ends.push(j);
              endlines.push(w + 1);
              found++;
              for (let t = i + 1; t < j; t++) wifelines[w + 1][t] = '~';
            } else {
              wifelines.push(Lines[line].slice()); wifelines[wifelines.length - 1][i] = ':';
              wifelines.push(Lines[line].slice()); wifelines[wifelines.length - 1][i] = ':';
              wifelines.push(Lines[line].slice()); wifelines[wifelines.length - 1][i] = 'L';
              wifelines[wifelines.length - 4][i] = 'D';
              Lines[line][j] = '';
              ends.push(j);
              for (let f2 = found; f2 >= 0; f2--) {
                wifelines[wifelines.length - 2 - f2][j] = '';
              }
              wifelines[wifelines.length - 2][j] = 'x' + wifelines[wifelines.length - 1][j];
              wifelines[wifelines.length - 1][j] = 'J';
              for (let t = i + 1; t < j; t++) wifelines[wifelines.length - 1][t] = '~';
              endlines.push(wifelines.length - 1);
            }
          }
        }
        break;
      }
    }

    for (let e = 0; e < ends.length; e++) {
      wifelines[endlines[e]][ends[e] - 1] = 'y';
      let k = 0;
      while (k < ends.length - e) {
        if (!wifelines[endlines[e] + 1 + k]) wifelines.push(Lines[line].slice());
        wifelines[endlines[e] + 1 + k][ends[e] - 1] = '!';
        k++;
      }
      if (!wifelines[endlines[e] + 1 + k]) wifelines.push(Lines[line].slice());
      wifelines[endlines[e] + 1 + k][ends[e] - 1] = '{' + recs[s].sub[ends.length - 1 - e] + '}';
    }

    for (let i = 0; i < LINE_WIDTH; i++) {
      for (let w2 = 0; w2 < wifelines.length; w2++) {
        wifelines[w2][i] = sanitizeTile(wifelines[w2][i]);
      }
    }

    if (oldlines.length) {
      for (let i = 0; i < oldlines.length; i++) {
        if (wifelines[i]) {
          for (let j = 0; j < wifelines[i].length; j++) {
            if (oldlines[i][j] !== '') wifelines[i][j] = oldlines[i][j];
          }
        } else {
          wifelines.push(oldlines[i]);
        }
      }
    }

    for (let i2 = 0; i2 < wifelines.length; i2++) {
      for (let j2 = 0; j2 < wifelines[i2].length; j2++) {
        if (wifelines[i2][j2] && wifelines[i2][j2][0] === '{') {
          wifelines[wifelines.length - 1][j2] = wifelines[i2][j2];
          for (let k2 = i2; k2 < wifelines.length - 1; k2++) wifelines[k2][j2] = '!';
        }
      }
    }

    const tp = [];
    let l = line;
    while (l < Lines.length) { tp.push(Lines[l]); l++; }
    let wIdx = wifelines.length - 1;
    let ln = 0;
    let lCopy = l;
    while (wIdx >= 0) {
      if (line < lCopy) {
        Lines[line + ln] = wifelines[wIdx];
        lCopy--;
        ln++;
      } else {
        Lines.push(wifelines[wIdx]);
      }
      wIdx--;
    }
    let k3 = 0;
    while (k3 < tp.length) {
      if (line < lCopy) {
        Lines[line + k3 + wifelines.length] = tp[k3];
        lCopy--;
      } else {
        Lines.push(tp[k3]);
      }
      k3++;
    }
    return line + wifelines.length;
  }

  function joinLevels(Lines) {
    for (let i = 0; i < Lines.length; i++) {
      for (let j = Lines[i].length - 1; j >= 0; j--) {
        const cell = Lines[i][j];
        if (!cell || cell[0] !== '[' || cell[cell.length - 1] !== ']') continue;
        const fcode = cell.substring(1, cell.length - 1);
        if (!Lines[i + 1]) continue;
        for (let k = 0; k < Lines[i + 1].length; k++) {
          const cell2 = Lines[i + 1][k];
          if (!cell2 || cell2[0] !== '{' || cell2[cell2.length - 1] !== '}') continue;
          const scode = cell2.substring(1, cell2.length - 1);
          if (fcode !== scode) continue;
          const newline = Lines[i].slice();
          if (k === j) {
            Lines[i][j] = '!';
            Lines[i + 1][k] = '!';
          } else if (j < k) {
            Lines[i][j] = ',';
            for (let m = j + 1; m < k; m++) Lines[i][m] = '-';
            Lines[i][k] = '\'';
            Lines[i + 1][k] = '!';
          } else {
            Lines[i][k] = '`';
            for (let m = k + 1; m < j; m++) Lines[i][m] = '-';
            Lines[i][j] = '.';
            Lines[i + 1][k] = '!';
          }
          for (let n = 0; n < Lines[i].length; n++) {
            if (Lines[i][n] && Lines[i][n][0] === '[') Lines[i][n] = '';
          }
          newline[j] = '';
          let newflag = false;
          for (let n = 0; n < newline.length; n++) {
            if (newline[n] && newline[n][0] === '[') { newflag = true; break; }
          }
          if (newflag) {
            Lines.push(Lines[Lines.length - 1]);
            for (let p = Lines.length - 2; p > i; p--) Lines[p] = Lines[p - 1];
            Lines[i + 1] = newline;
          }
        }
      }
    }
  }

  function removeExtraLinks(Lines) {
    for (let i = 0; i < Lines.length; i++) {
      for (let k = 0; k < Lines[i].length; k++) {
        if (Lines[i][k] && Lines[i][k][0] === '{') {
          Lines[i][k] = '';
          let t = i + 1;
          while (t < Lines.length && Lines[t][k] !== 'y') {
            Lines[t][k] = '';
            t++;
          }
          if (Lines[t]) Lines[t][k] = Lines[t][k - 1];
        }
        if (Lines[i][k] && Lines[i][k][0] === '[') {
          Lines[i][k] = '';
          let t = i - 1;
          while (t >= 0 && Lines[t][k] === '!') {
            Lines[t][k] = '';
            t--;
          }
        }
      }
    }
  }

  function joinBrokenLines(Lines) {
    for (let i = Lines.length - 1; i >= 0; i--) {
      for (let j = 0; j < Lines[i].length; j++) {
        const c = Lines[i][j];
        if (c === '!') {
          let k = i - 1;
          while (k >= 0 && Lines[k][j] === '') { Lines[k][j] = '!'; k--; }
          k = i + 1;
          while (k < Lines.length && Lines[k][j] === '') { Lines[k][j] = '!'; k++; }
        }
        if (c === ',' || c === '.') {
          let k = i - 1;
          while (k >= 0 && Lines[k][j] === '') { Lines[k][j] = '!'; k--; }
          while (k >= 0 && Lines[k] && Lines[k][j] === '-') { Lines[k][j] = '*'; k--; }
        }
        if (c === '`' || c === '\'') {
          let k = i + 1;
          while (k < Lines.length && Lines[k][j] === '') { Lines[k][j] = '!'; k++; }
          while (k < Lines.length && Lines[k] && Lines[k][j] === '-') { Lines[k][j] = '*'; k++; }
        }
      }
    }
  }

  function replaceCodes(Lines, members, recs) {
    let m = 1;
    for (let i = 0; i < Lines.length; i++) {
      for (let k = 0; k < Lines[i].length; k++) {
        for (let j = 0; j < members.length; j++) {
          if (members[j] === Lines[i][k]) {
            const idx = Lines[i].length - m;
            Lines[i][idx] = recs[members[j]].boxtext + '=[[' + recs[members[j]].text.trim() + ']]';
            Lines[i][k] = recs[members[j]].boxtext.trim();
            m++;
          }
        }
      }
      Lines[i][0] = '{{familytree ' + Lines[i][0];
      Lines[i][Lines[i].length - 1] = Lines[i][Lines[i].length - 1] + '}}';
    }
  }

  function replaceSpaces(Lines) {
    for (let i = 0; i < Lines.length; i++) {
      for (let k = 0; k < Lines[i].length; k++) {
        if (Lines[i][k] === '') Lines[i][k] = '| ';
        else if (k !== 0) Lines[i][k] = '|' + Lines[i][k];
      }
    }
  }

  function buildOutput(Lines) {
    // Trim leading empty columns (shift output left)
    let j = 2;
    let flag = false;
    while (j < Lines[0].length) {
      for (let i = 0; i < Lines.length; i++) {
        if (Lines[i][j] !== '| ') { flag = true; break; }
      }
      if (flag) break;
      j++;
    }
    if (flag && j > 2) {
      const shift = j - 2;
      for (let i = 0; i < Lines.length; i++) {
        let k = 2;
        while (k < Lines[i].length - 1 - shift) {
          Lines[i][k] = Lines[i][k + shift];
          k++;
        }
        while (k < Lines[i].length - 1) {
          Lines[i][k] = '| ';
          k++;
        }
      }
    }
    // Find last NA tile column across all rows
    let lastindex = 0;
    for (let i = 0; i < Lines.length; i++) {
      for (let j2 = Lines[i].length - 1; j2 >= 0; j2--) {
        if (Lines[i][j2] && Lines[i][j2].substring(1, 3) === 'NA') {
          if (lastindex < j2) lastindex = j2;
          break;
        }
      }
    }
    let warning = '';
    if (lastindex > 80) {
      warning = 'WARNING: Output exceeds the Wikipedia Template:Family_tree maximum of 80 tiles per row. ' +
                'The rendered tree may appear unexpected. See: ' +
                'https://en.wikipedia.org/wiki/Template:Family_tree';
    }
    const rows = ['{{familytree/start}}'];
    for (let i = Lines.length - 1; i >= 0; i--) {
      let rowStr = '';
      let j2 = 0;
      while (j2 < Lines[i].length) {
        rowStr += Lines[i][j2];
        if (Lines[i][j2] && Lines[i][j2].substring(1, 3) === 'NA') j2 += 3;
        else j2++;
      }
      rows.push(rowStr);
    }
    rows.push('{{familytree/end}}');
    return { output: rows.join('\n') + '\n', warning };
  }

  function gedcom2wiki(text) {
    const recs = parseRecords(text);
    buildChildren(recs);
    linkFamilies(recs);
    buildDown(recs);
    assignLevels(recs);
    const { levels, members } = buildLevels(recs);
    const maxLevel = interleaveMarital(recs, levels);

    const Lines = [];
    for (let i = levels.length - 1; i >= 0; i--) {
      const level = levels[i];
      let line = defaultLine();
      let linestart = Math.abs(maxLevel / 2 + 2 - level.length / 2);
      linestart = Math.floor(linestart);
      let oldfamily = '';
      for (let j = 0; j < level.length; j++) {
        line[linestart] = level[j];
        let family = '';
        const arr = recs[level[j]].childs;
        for (let a = 0; a < arr.length; a++) {
          if (recs[arr[a]].tag.trim() === 'FAMS') { family = recs[arr[a]].val; break; }
        }
        linestart += (family === oldfamily) ? 4 : 6;
        oldfamily = family;
      }
      Lines.push(line);
      let curline = Lines.length - 1;
      const family = [];
      let j = 0;
      while (j < level.length) {
        const arr = recs[level[j]].up;
        for (let a = 0; a < arr.length; a++) {
          if (family.indexOf(arr[a]) === -1) {
            family.push(arr[a]);
            curline = makeRelation(Lines, recs, arr[a], curline);
          }
        }
        if (recs[level[j]].marital.length > 0) {
          let husband = recs[level[j]].marital[0];
          const wifes = [];
          do {
            wifes.push(level[j]);
            husband = recs[level[j]].marital[0];
            j++;
          } while (j < level.length &&
                   recs[level[j]] && recs[level[j]].marital.length > 0 &&
                   recs[level[j]].marital[0] === husband);
          curline = makeMarital(Lines, recs, husband, wifes, curline);
        }
        j++;
      }
    }

    joinLevels(Lines);
    removeExtraLinks(Lines);
    joinBrokenLines(Lines);
    replaceCodes(Lines, members, recs);
    replaceSpaces(Lines);

    const { output, warning } = buildOutput(Lines);
    const individualCount = recs.filter(r => r.tag.trim() === 'INDI').length;
    const familyCount = recs.filter(r => r.tag.trim() === 'FAM').length;
    return { output, warning, individualCount, familyCount };
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = gedcom2wiki;
  } else {
    root.gedcom2wiki = gedcom2wiki;
  }
})(typeof window !== 'undefined' ? window : globalThis);
