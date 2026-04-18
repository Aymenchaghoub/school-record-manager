const tones = {
  success: {
    background: '#DCFCE7',
    color: '#15803D',
    borderColor: '#BBF7D0',
  },
  danger: {
    background: '#FEE2E2',
    color: '#DC2626',
    borderColor: '#FECACA',
  },
  warning: {
    background: '#FEF9C3',
    color: '#A16207',
    borderColor: '#FDE68A',
  },
  info: {
    background: '#EDE9FE',
    color: '#7C3AED',
    borderColor: '#DDD6FE',
  },
  neutral: {
    background: '#F1F5F9',
    color: '#475569',
    borderColor: '#E2E8F0',
  },
};

const toneAlias = {
  brand: 'info',
  slate: 'neutral',
};

export function Badge({ tone = 'slate', children }) {
  const normalizedTone = toneAlias[tone] || tone;
  const style = tones[normalizedTone] || tones.neutral;

  return (
    <span className="ui-badge" style={style}>
      {children}
    </span>
  );
}
