export type IconsId =
  | "accessibility"
  | "close"
  | "grayscale"
  | "grayscale2"
  | "invert"
  | "line-height-decrease"
  | "line-height-increase"
  | "mouse"
  | "no-animations"
  | "reset"
  | "ruler"
  | "signspace"
  | "signspacedecrease"
  | "signspaceincrease"
  | "speak"
  | "textdecrease"
  | "textincrease"
  | "underline";

export type IconsKey =
  | "Accessibility"
  | "Close"
  | "Grayscale"
  | "Grayscale2"
  | "Invert"
  | "LineHeightDecrease"
  | "LineHeightIncrease"
  | "Mouse"
  | "NoAnimations"
  | "Reset"
  | "Ruler"
  | "Signspace"
  | "Signspacedecrease"
  | "Signspaceincrease"
  | "Speak"
  | "Textdecrease"
  | "Textincrease"
  | "Underline";

export enum Icons {
  Accessibility = "accessibility",
  Close = "close",
  Grayscale = "grayscale",
  Grayscale2 = "grayscale2",
  Invert = "invert",
  LineHeightDecrease = "line-height-decrease",
  LineHeightIncrease = "line-height-increase",
  Mouse = "mouse",
  NoAnimations = "no-animations",
  Reset = "reset",
  Ruler = "ruler",
  Signspace = "signspace",
  Signspacedecrease = "signspacedecrease",
  Signspaceincrease = "signspaceincrease",
  Speak = "speak",
  Textdecrease = "textdecrease",
  Textincrease = "textincrease",
  Underline = "underline",
}

export const ICONS_CODEPOINTS: { [key in Icons]: string } = {
  [Icons.Accessibility]: "61697",
  [Icons.Close]: "61698",
  [Icons.Grayscale]: "61699",
  [Icons.Grayscale2]: "61700",
  [Icons.Invert]: "61701",
  [Icons.LineHeightDecrease]: "61702",
  [Icons.LineHeightIncrease]: "61703",
  [Icons.Mouse]: "61704",
  [Icons.NoAnimations]: "61705",
  [Icons.Reset]: "61706",
  [Icons.Ruler]: "61707",
  [Icons.Signspace]: "61708",
  [Icons.Signspacedecrease]: "61709",
  [Icons.Signspaceincrease]: "61710",
  [Icons.Speak]: "61711",
  [Icons.Textdecrease]: "61712",
  [Icons.Textincrease]: "61713",
  [Icons.Underline]: "61714",
};
