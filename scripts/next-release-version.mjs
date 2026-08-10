import { execFileSync } from 'node:child_process';
import { appendFileSync } from 'node:fs';

const tagPattern = /^v(\d+)\.(\d+)\.(\d+)$/;
const tags = execFileSync('git', ['tag', '--list', 'v*', '--sort=-v:refname'], {
  encoding: 'utf8',
})
  .trim()
  .split('\n')
  .filter((tag) => tagPattern.test(tag));
const lastTag = tags[0] ?? null;
const range = lastTag ? `${lastTag}..HEAD` : 'HEAD';
const commits = execFileSync('git', ['log', range, '--format=%B%x00'], {
  encoding: 'utf8',
})
  .split('\u0000')
  .filter(Boolean);

if (lastTag && commits.length === 0) {
  process.exit(0);
}

const version = lastTag
  ? nextVersion(lastTag, commits)
  : 'v0.1.0';
const output = `version=${version}\n`;

if (process.env.GITHUB_OUTPUT) {
  appendFileSync(process.env.GITHUB_OUTPUT, output);
} else {
  process.stdout.write(output);
}

function nextVersion(tag, messages) {
  const [, major, minor, patch] = tag.match(tagPattern).map(Number);
  const bump = messages.some(isBreakingChange)
    ? 'major'
    : messages.some(isFeature)
      ? 'minor'
      : 'patch';

  if (bump === 'major') {
    return `v${major + 1}.0.0`;
  }

  if (bump === 'minor') {
    return `v${major}.${minor + 1}.0`;
  }

  return `v${major}.${minor}.${patch + 1}`;
}

function isBreakingChange(message) {
  return /BREAKING CHANGE:/.test(message) || /^[a-z]+(?:\([^\n)]*\))?!:/m.test(message);
}

function isFeature(message) {
  return /^feat(?:\([^\n)]*\))?:/m.test(message);
}
