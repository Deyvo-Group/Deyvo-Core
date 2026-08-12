import { basicSetup } from 'codemirror';
import { indentWithTab } from '@codemirror/commands';
import { html } from '@codemirror/lang-html';
import { EditorState } from '@codemirror/state';
import { oneDark } from '@codemirror/theme-one-dark';
import { EditorView, keymap } from '@codemirror/view';

export const createHtmlEditor = ({ parent, value, onUpdate }) => new EditorView({
  state: EditorState.create({
    doc: value,
    extensions: [
      basicSetup,
      html({ autoCloseTags: true }),
      oneDark,
      EditorView.lineWrapping,
      keymap.of([indentWithTab]),
      EditorView.updateListener.of((update) => {
        if (update.docChanged) {
          onUpdate(update.state.doc.toString());
        }
      })
    ]
  }),
  parent
});
